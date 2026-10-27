<?php

namespace App\Filament\Admin\Pages;

use App\Models\Vault;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\SalaryPayment;
use App\Models\VaultTransfer;
use Filament\Pages\Page;
use App\Models\Enrollment;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VaultReport extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected string $view = 'filament.admin.pages.vault-report';

    public ?array $data = [];
    public $reportData = null;

    public static function canAccess(): bool
    {
        return auth()->check() && (auth()->user()->is_super_admin || auth()->user()->can('view_vault_transactions'));
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-chart-bar';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament/admin/vault_report.navigation_label');
    }

    public function getTitle(): string
    {
        return __('filament/admin/vault_report.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return 'التقارير المالية';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public function mount(): void
    {
        $this->form->fill([
            'vault_id' => null,
            'from_date' => now()->startOfMonth()->format('Y-m-d'),
            'to_date' => now()->format('Y-m-d'),
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('vault_id')
                ->label(__('filament/admin/vault_report.vault'))
                ->options(function () {
                    $user = auth()->user();
                    return Vault::where('is_active', true)
                        ->when($user?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid))
                        ->get()
                        ->mapWithKeys(fn($v) => [$v->id => $v->getDisplayNameWithBalance()]);
                })
                ->required()
                ->searchable(),

            DatePicker::make('from_date')
                ->label(__('filament/admin/vault_report.from_date'))
                ->required()
                ->default(now()->startOfMonth()),

            DatePicker::make('to_date')
                ->label(__('filament/admin/vault_report.to_date'))
                ->required()
                ->default(now()),
        ];
    }

    protected function getFormColumns(): int|string|array
    {
        return 3;
    }

    public function generateReport()
    {
        $this->validate([
            'data.vault_id' => 'required|exists:vaults,id',
            'data.from_date' => 'required|date',
            'data.to_date' => 'required|date|after_or_equal:data.from_date',
        ]);

        $vault = Vault::findOrFail($this->data['vault_id']);
        if (!auth()->user()->is_super_admin && auth()->user()->tenant_id && (int) $vault->tenant_id !== (int) auth()->user()->tenant_id) {
            abort(403, 'غير مصرح لك بعرض تقرير هذه الخزنة');
        }
        $fromDate = Carbon::parse($this->data['from_date'])->startOfDay();
        $toDate = Carbon::parse($this->data['to_date'])->endOfDay();

        // الرصيد الافتتاحي (الرصيد قبل تاريخ البداية)
        $openingBalance = $this->calculateOpeningBalance($vault, $fromDate);

        // جمع كل العمليات
        $transactions = collect();

        // عمليات الدفع من التسجيلات
        $enrollments = Enrollment::where('vault_id', $vault->id)
            ->whereBetween('enrollment_date', [$fromDate, $toDate])
            ->where('paid_amount', '>', 0)
            ->with('student.user')
            ->get();

        foreach ($enrollments as $enrollment) {
            $transactions->push([
                'date' => $enrollment->enrollment_date,
                'type' => 'إيراد - تسجيل',
                'amount' => $enrollment->paid_amount,
                'description' => 'دفعة مقدمة - ' . ($enrollment->student->user->name ?? 'طالب'),
                'reference' => $enrollment->enrollment_code,
                'is_income' => true,
            ]);
        }

        // المدفوعات
        $payments = Payment::where('vault_id', $vault->id)
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereBetween('payment_date', [$fromDate, $toDate])
                    ->orWhere(function ($fallback) use ($fromDate, $toDate) {
                        $fallback->whereNull('payment_date')
                            ->whereBetween('created_at', [$fromDate, $toDate]);
                    });
            })
            ->where('status', 'completed')
            ->with('enrollment.student.user')
            ->get();

        foreach ($payments as $payment) {
            $transactions->push([
                'date' => $payment->payment_date,
                'type' => 'إيراد - دفعة',
                'amount' => $payment->amount,
                'description' => 'دفعة - ' . ($payment->enrollment->student->user->name ?? 'طالب'),
                'reference' => $payment->payment_code,
                'is_income' => true,
            ]);
        }

        // المصروفات
        $expenses = Expense::where('vault_id', $vault->id)
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->with('category')
            ->get();

        foreach ($expenses as $expense) {
            $transactions->push([
                'date' => $expense->expense_date,
                'type' => 'مصروف',
                'amount' => $expense->amount,
                'description' => ($expense->category->name ?? 'مصروف') . ' - ' . ($expense->description ?? ''),
                'reference' => $expense->expense_code,
                'is_income' => false,
            ]);
        }

        // مرتبات الموظفين/المدرسين المصروفة من الخزينة
        $salaryPayments = SalaryPayment::where('vault_id', $vault->id)
            ->where('status', 'paid')
            ->where(function ($query) use ($fromDate, $toDate) {
                $query->whereDate('payment_date', '>=', $fromDate->toDateString())
                    ->whereDate('payment_date', '<=', $toDate->toDateString())
                    ->orWhere(function ($fallback) use ($fromDate, $toDate) {
                        $fallback->whereNull('payment_date')
                            ->whereBetween('created_at', [$fromDate, $toDate]);
                    });
            })
            ->with(['instructor.user', 'admin.user'])
            ->get();

        foreach ($salaryPayments as $salaryPayment) {
            $transactions->push([
                'date' => $salaryPayment->payment_date,
                'type' => 'مصروف - راتب',
                'amount' => $salaryPayment->amount,
                'description' => 'صرف راتب - ' . $salaryPayment->employee_name,
                'reference' => 'SAL-' . $salaryPayment->id,
                'is_income' => false,
            ]);
        }

        // التحويلات بين الخزائن (واردة وصادرة)
        $incomingTransfers = VaultTransfer::where('to_vault_id', $vault->id)
            ->where('status', 'completed')
            ->whereDate('transfer_date', '>=', $fromDate->toDateString())
            ->whereDate('transfer_date', '<=', $toDate->toDateString())
            ->with('fromVault')
            ->get();

        foreach ($incomingTransfers as $transfer) {
            $transactions->push([
                'date' => $transfer->transfer_date,
                'type' => 'وارد - تحويل خزنة',
                'amount' => $transfer->amount,
                'description' => 'تحويل وارد من ' . ($transfer->fromVault?->name ?? 'خزنة أخرى'),
                'reference' => $transfer->transfer_code,
                'is_income' => true,
            ]);
        }

        $outgoingTransfers = VaultTransfer::where('from_vault_id', $vault->id)
            ->where('status', 'completed')
            ->whereDate('transfer_date', '>=', $fromDate->toDateString())
            ->whereDate('transfer_date', '<=', $toDate->toDateString())
            ->with('toVault')
            ->get();

        foreach ($outgoingTransfers as $transfer) {
            $transactions->push([
                'date' => $transfer->transfer_date,
                'type' => 'مصروف - تحويل خزنة',
                'amount' => $transfer->amount,
                'description' => 'تحويل صادر إلى ' . ($transfer->toVault?->name ?? 'خزنة أخرى'),
                'reference' => $transfer->transfer_code,
                'is_income' => false,
            ]);
        }

        // ترتيب العمليات حسب التاريخ
        $transactions = $transactions->sortBy('date')->values();

        // حساب الإجماليات
        $totalIncome = $transactions->where('is_income', true)->sum('amount');
        $totalExpenses = $transactions->where('is_income', false)->sum('amount');
        $closingBalance = $openingBalance + $totalIncome - $totalExpenses;

        $this->reportData = [
            'vault' => $vault,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'opening_balance' => $openingBalance,
            'transactions' => $transactions,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'closing_balance' => $closingBalance,
        ];

        // ضمان الاتساق مع معادلة الرصيد التراكمي للخزنة حتى تاريخ نهاية التقرير.
        $this->reportData['closing_balance'] = $this->calculateBalanceUntilDate($vault, $toDate);
    }

    private function calculateOpeningBalance(Vault $vault, Carbon $beforeDate): float
    {
        $previousDate = $beforeDate->copy()->subDay();
        return $this->calculateBalanceUntilDate($vault, $previousDate);
    }

    private function calculateBalanceUntilDate(Vault $vault, Carbon $date): float
    {
        $asOf = $date->toDateString();

        $revenues = $vault->revenues()
            ->whereDate('revenue_date', '<=', $asOf)
            ->sum('amount');

        $studentPayments = 0;
        if (\Schema::hasTable('student_payments')) {
            $studentPayments = $vault->studentPayments()
                ->whereDate('payment_date', '<=', $asOf)
                ->where('status', 'completed')
                ->sum('net_amount');
        }

        $enrollmentPayments = $vault->enrollments()
            ->where(function ($query) use ($asOf) {
                $query->whereDate('enrollment_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('enrollment_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->sum('paid_amount');

        $payments = $vault->payments()
            ->where(function ($query) use ($asOf) {
                $query->whereDate('payment_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('payment_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->where('status', 'completed')
            ->sum('amount');

        $expenses = $vault->expenses()
            ->whereDate('expense_date', '<=', $asOf)
            ->sum('amount');

        $salaryPayments = $vault->salaryPayments()
            ->where('status', 'paid')
            ->where(function ($query) use ($asOf) {
                $query->whereDate('payment_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('payment_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->sum('amount');

        $transfersIn = $vault->transfersTo()
            ->where('status', 'completed')
            ->where(function ($query) use ($asOf) {
                $query->whereDate('transfer_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('transfer_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->sum('amount');

        $transfersOut = $vault->transfersFrom()
            ->where('status', 'completed')
            ->where(function ($query) use ($asOf) {
                $query->whereDate('transfer_date', '<=', $asOf)
                    ->orWhere(function ($fallback) use ($asOf) {
                        $fallback->whereNull('transfer_date')
                            ->whereDate('created_at', '<=', $asOf);
                    });
            })
            ->sum('amount');

        return (float) $vault->opening_balance
            + (float) $revenues
            + (float) $studentPayments
            + (float) $enrollmentPayments
            + (float) $payments
            + (float) $transfersIn
            - (float) $expenses
            - (float) $salaryPayments
            - (float) $transfersOut;
    }

    public function getFormStatePath(): string
    {
        return 'data';
    }

    public function exportToExcel(): StreamedResponse
    {
        if (!$this->reportData) {
            $this->generateReport();
        }

        $data = $this->reportData;

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['تقرير الخزينة']);
            fputcsv($handle, ['الخزينة', $data['vault']->name ?? '']);
            fputcsv($handle, ['من التاريخ', $data['from_date']->format('Y-m-d')]);
            fputcsv($handle, ['إلى التاريخ', $data['to_date']->format('Y-m-d')]);
            fputcsv($handle, ['تاريخ التقرير', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['الرصيد الافتتاحي', number_format($data['opening_balance'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['التاريخ', 'النوع', 'المبلغ', 'الوصف', 'المرجع']);

            foreach ($data['transactions'] as $transaction) {
                fputcsv($handle, [
                    $transaction['date'],
                    $transaction['type'],
                    number_format($transaction['amount'], 2),
                    $transaction['description'],
                    $transaction['reference']
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['الإجمالي - الإيرادات', number_format($data['total_income'], 2)]);
            fputcsv($handle, ['الإجمالي - المصروفات', number_format($data['total_expenses'], 2)]);
            fputcsv($handle, ['الرصيد الإغلاقي', number_format($data['closing_balance'], 2)]);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="تقرير-الخزينة-' . now()->format('Y-m-d-H-i-s') . '.csv"'
        ]);
    }

    public function exportToPdf()
    {
        if (!$this->reportData) {
            $this->generateReport();
        }

        $html = view('filament.admin.pages.vault-report-pdf', [
            'data' => $this->reportData
        ])->render();

        $filename = 'تقرير-الخزينة-' . now()->format('Y-m-d-H-i-s') . '.pdf';

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->WriteHTML($html);

        return response()->stream(
            function () use ($mpdf) {
                echo $mpdf->Output('', 'S');
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]
        );
    }
}

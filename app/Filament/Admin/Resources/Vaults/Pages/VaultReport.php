<?php

namespace App\Filament\Admin\Resources\Vaults\Pages;

use App\Filament\Admin\Resources\Vaults\VaultResource;
use App\Models\Vault;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\SalaryPayment;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Carbon;

class VaultReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = VaultResource::class;

    protected string $view = 'filament.admin.resources.vaults.pages.vault-report';

    public ?array $data = [];
    public $reportData = null;

    public function getTitle(): string
    {
        return __('filament/admin/vault_resource.report.title');
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
                ->label(__('filament/admin/vault_resource.report.vault'))
                ->options(Vault::where('is_active', true)->pluck('name', 'id'))
                ->required()
                ->searchable()
                ->live(),

            DatePicker::make('from_date')
                ->label(__('filament/admin/vault_resource.report.from_date'))
                ->required()
                ->default(now()->startOfMonth()),

            DatePicker::make('to_date')
                ->label(__('filament/admin/vault_resource.report.to_date'))
                ->required()
                ->default(now()),
        ];
    }

    public function generateReport()
    {
        $this->validate([
            'data.vault_id' => 'required|exists:vaults,id',
            'data.from_date' => 'required|date',
            'data.to_date' => 'required|date|after_or_equal:data.from_date',
        ]);

        $vault = Vault::findOrFail($this->data['vault_id']);
        $fromDate = Carbon::parse($this->data['from_date'])->startOfDay();
        $toDate = Carbon::parse($this->data['to_date'])->endOfDay();

        // الرصيد الافتتاحي (الرصيد قبل تاريخ البداية)
        $openingBalance = $this->calculateOpeningBalance($vault, $fromDate);

        // جمع كل العمليات
        $transactions = collect();

        // عمليات الدفع من التسجيلات (المقدم المباشر دون احتساب أقساط مسجلة كدفعات)
        $enrollments = Enrollment::where('vault_id', $vault->id)
            ->whereBetween('enrollment_date', [$fromDate, $toDate])
            ->where('paid_amount', '>', 0)
            ->with('student.user')
            ->get();

        foreach ($enrollments as $enrollment) {
            $upfront = $enrollment->getCalculatedUpfrontAmount();
            $recordedPayments = (float) $enrollment->payments()
                ->where('vault_id', $vault->id)
                ->where('status', 'completed')
                ->sum('amount');
            $directUpfront = max(0, $upfront - $recordedPayments);

            if ($directUpfront > 0) {
                $transactions->push([
                    'date' => $enrollment->enrollment_date,
                    'type' => 'إيراد - تسجيل',
                    'amount' => $directUpfront,
                    'description' => 'دفعة مقدمة - ' . ($enrollment->student->user->name ?? 'طالب'),
                    'reference' => $enrollment->enrollment_code,
                    'is_income' => true,
                ]);
            }
        }

        // المدفوعات
        $payments = Payment::where('vault_id', $vault->id)
            ->whereBetween('payment_date', [$fromDate, $toDate])
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
            ->whereDate('payment_date', '>=', $fromDate->toDateString())
            ->whereDate('payment_date', '<=', $toDate->toDateString())
            ->with(['instructor.user', 'admin.user'])
            ->get();

        foreach ($salaryPayments as $salaryPayment) {
            $employeeName = $salaryPayment->employee_name;

            $transactions->push([
                'date' => $salaryPayment->payment_date,
                'type' => 'مصروف - راتب',
                'amount' => $salaryPayment->amount,
                'description' => 'صرف راتب - ' . $employeeName,
                'reference' => 'SAL-' . $salaryPayment->id,
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
    }

    private function calculateOpeningBalance(Vault $vault, Carbon $beforeDate): float
    {
        $openingBalance = (float) $vault->opening_balance;

        // الإيرادات قبل تاريخ البداية من جدول revenues (المستقلة فقط)
        $revenues = (float) $vault->revenues()
            ->whereNull('payment_id')
            ->where('revenue_date', '<', $beforeDate)
            ->sum('amount');

        // المدفوعات القديمة من جدول student_payments (إذا كان موجود)
        $studentPayments = 0;
        if (\Schema::hasTable('student_payments')) {
            $studentPayments = (float) $vault->studentPayments()
                ->where('payment_date', '<', $beforeDate)
                ->where('status', 'completed')
                ->sum('net_amount');
        }

        // المدفوعات قبل تاريخ البداية من جدول payments
        $payments = (float) $vault->payments()
            ->where('payment_date', '<', $beforeDate)
            ->where('status', 'completed')
            ->sum('amount');

        // المبالغ المقدمة من التسجيلات قبل تاريخ البداية
        $enrollmentPayments = 0.0;
        $beforeEnrollments = $vault->enrollments()
            ->where('enrollment_date', '<', $beforeDate)
            ->get();

        foreach ($beforeEnrollments as $enrollment) {
            $upfront = $enrollment->getCalculatedUpfrontAmount();
            $recordedPayments = (float) $enrollment->payments()
                ->where('vault_id', $vault->id)
                ->where('status', 'completed')
                ->where('payment_date', '<', $beforeDate)
                ->sum('amount');
            if ($upfront > $recordedPayments) {
                $enrollmentPayments += ($upfront - $recordedPayments);
            }
        }

        // المصروفات قبل تاريخ البداية
        $expenses = (float) $vault->expenses()
            ->where('expense_date', '<', $beforeDate)
            ->sum('amount');

        // المرتبات المصروفة قبل تاريخ البداية
        $salaryPayments = (float) $vault->salaryPayments()
            ->where('status', 'paid')
            ->whereDate('payment_date', '<', $beforeDate->toDateString())
            ->sum('amount');

        // التحويلات الواردة والصادرة
        $transfersIn = (float) $vault->transfersTo()
            ->where('status', 'completed')
            ->where('transfer_date', '<', $beforeDate)
            ->sum('amount');

        $transfersOut = (float) $vault->transfersFrom()
            ->where('status', 'completed')
            ->where('transfer_date', '<', $beforeDate)
            ->sum('amount');

        // الرصيد الافتتاحي = الرصيد الأساسي + كل الإيرادات - كل المصروفات قبل التاريخ
        return round($openingBalance + $revenues + $studentPayments + $enrollmentPayments + $payments + $transfersIn - $expenses - $salaryPayments - $transfersOut, 2);
    }

    public function getFormStatePath(): string
    {
        return 'data';
    }
}

<?php

namespace App\Filament\Admin\Pages\Reports;

use Filament\Pages\Page;
use App\Models\Course;
use App\Models\Group;
use App\Models\Student;
use App\Models\Installment;
use Illuminate\Database\Eloquent\Builder;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AllInstallmentsReport extends Page
{
    use HasPageShield;

    protected string $view = 'filament.admin.pages.reports.all-installments-report';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';

    protected static \UnitEnum|string|null $navigationGroup = 'التقارير المالية';
    
    protected static ?int $navigationSort = 4;

    public $course_id = null;
    public $group_id = null;
    public $student_id = null;
    public $status = [];
    public $date_from = null;
    public $date_to = null;
    public $display_type = 'all';
    public $time_filter = 'all'; // all, past, today, future

    public static function canAccess(): bool
    {
        return true;
    }

    public function mount(): void
    {
        //
    }
    
    public function applyFilters(): void
    {
        // Trigger re-render
    }
    
    public function getCourses()
    {
        return Course::where('tenant_id', auth()->user()->tenant_id)->get();
    }
    
    public function getGroups()
    {
        return Group::where('tenant_id', auth()->user()->tenant_id)->get();
    }
    
    public function getStudents()
    {
        return Student::with('user')->where('tenant_id', auth()->user()->tenant_id)->get();
    }

    public static function getNavigationLabel(): string
    {
        return 'تقرير جميع الأقساط';
    }

    public function getTitle(): string
    {
        return 'تقرير شامل للأقساط';
    }

    public function getHeading(): string
    {
        return 'تقرير شامل للأقساط المستحقة والقريبة';
    }

    public function getInstallments()
    {
        $query = Installment::query()
            ->with([
                'enrollment.student.user',
                'enrollment.groups.course',
                'enrollment.payments'
            ]);

        // Filter by course
        if ($this->course_id) {
            $query->whereHas('enrollment.groups.course', function (Builder $q) {
                $q->where('courses.id', $this->course_id);
            });
        }

        // Filter by group
        if ($this->group_id) {
            $query->whereHas('enrollment.groups', function (Builder $q) {
                $q->where('groups.id', $this->group_id);
            });
        }

        // Filter by student
        if ($this->student_id) {
            $query->whereHas('enrollment.student', function (Builder $q) {
                $q->where('student_id', $this->student_id);
            });
        }

        // Filter by date range
        if ($this->date_from) {
            $query->where('due_date', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->where('due_date', '<=', $this->date_to);
        }

        // Filter by time (past, today, future)
        if ($this->time_filter === 'past') {
            $query->where('due_date', '<', now()->startOfDay());
        } elseif ($this->time_filter === 'today') {
            $query->whereDate('due_date', '=', now()->toDateString());
        } elseif ($this->time_filter === 'future') {
            $query->where('due_date', '>', now()->endOfDay());
        }

        // Filter by display type
        if ($this->display_type === 'overdue') {
            $query->overdue();
        } elseif ($this->display_type === 'upcoming') {
            $query->whereIn('status', ['pending', 'partial'])
                ->whereBetween('due_date', [now()->startOfDay(), now()->addDays(7)->endOfDay()]);
        } elseif ($this->display_type === 'partial') {
            $query->where('status', 'partial');
        }

        // Filter by status
        if (is_array($this->status) && count($this->status) > 0) {
            $query->whereIn('status', $this->status);
        }

        return $query->orderBy('due_date', 'asc')->get()
            ->map(function ($installment) {
                $installment->payment_status = $this->calculatePaymentStatus($installment);
                return $installment;
            });
    }

    private function calculatePaymentStatus($installment)
    {
        if ($installment->status === 'cancelled') {
            return ['status' => 'ملغي / معفى', 'color' => 'gray', 'icon' => '🚫'];
        } elseif ($installment->status === 'paid') {
            return ['status' => 'مكتمل', 'color' => 'green', 'icon' => '✅'];
        } elseif ($installment->status === 'partial') {
            return ['status' => 'سداد جزئي', 'color' => 'yellow', 'icon' => '⚠️'];
        } elseif ($installment->status === 'overdue' || ($installment->due_date < now() && $installment->status === 'pending')) {
            return ['status' => 'متأخر', 'color' => 'red', 'icon' => '🔔'];
        } else {
            return ['status' => 'قيد الانتظار', 'color' => 'blue', 'icon' => '⏰'];
        }
    }

    public function getStats()
    {
        $installments = $this->getInstallments();

        return [
            'total_count' => $installments->count(),
            'total_amount' => $installments->sum('amount'),
            'total_paid' => $installments->sum('paid_amount'),
            'total_remaining' => $installments->sum(fn($i) => $i->amount - $i->paid_amount),
            'overdue_count' => $installments->filter(fn($i) => $i->isOverdue())->count(),
            'partial_count' => $installments->filter(fn($i) => $i->isPartiallyPaid())->count(),
        ];
    }

    public function getPastInstallmentsData()
    {
        $installments = Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->where('due_date', '<', now()->startOfDay());

        // Apply filters
        $this->applyCommonFilters($installments);

        $data = $installments->get();

        return [
            'count' => $data->count(),
            'total_amount' => $data->sum('amount'),
            'total_paid' => $data->sum('paid_amount'),
            'total_remaining' => $data->sum(fn($i) => $i->amount - $i->paid_amount),
        ];
    }

    public function getTodayInstallmentsData()
    {
        $installments = Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->whereDate('due_date', '=', now()->toDateString());

        // Apply filters
        $this->applyCommonFilters($installments);

        $data = $installments->get();

        return [
            'count' => $data->count(),
            'total_amount' => $data->sum('amount'),
            'total_paid' => $data->sum('paid_amount'),
            'total_remaining' => $data->sum(fn($i) => $i->amount - $i->paid_amount),
        ];
    }

    public function getFutureInstallmentsData()
    {
        $installments = Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->where('due_date', '>', now()->endOfDay());

        // Apply filters
        $this->applyCommonFilters($installments);

        $data = $installments->get();

        return [
            'count' => $data->count(),
            'total_amount' => $data->sum('amount'),
            'total_paid' => $data->sum('paid_amount'),
            'total_remaining' => $data->sum(fn($i) => $i->amount - $i->paid_amount),
        ];
    }

    private function applyCommonFilters($query)
    {
        // Filter by course
        if ($this->course_id) {
            $query->whereHas('enrollment.groups.course', function (Builder $q) {
                $q->where('courses.id', $this->course_id);
            });
        }

        // Filter by group
        if ($this->group_id) {
            $query->whereHas('enrollment.groups', function (Builder $q) {
                $q->where('groups.id', $this->group_id);
            });
        }

        // Filter by student
        if ($this->student_id) {
            $query->whereHas('enrollment.student', function (Builder $q) {
                $q->where('student_id', $this->student_id);
            });
        }

        // Filter by date range
        if ($this->date_from) {
            $query->where('due_date', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->where('due_date', '<=', $this->date_to);
        }

        // Filter by status
        if (is_array($this->status) && count($this->status) > 0) {
            $query->whereIn('status', $this->status);
        }

        return $query;
    }

    protected function getViewData(): array
    {
        return [
            'installments' => $this->getInstallments(),
            'stats' => $this->getStats(),
            'pastStats' => $this->getPastInstallmentsData(),
            'todayStats' => $this->getTodayInstallmentsData(),
            'futureStats' => $this->getFutureInstallmentsData(),
        ];
    }

    public function exportToExcel(): StreamedResponse
    {
        $installments = $this->getInstallments();
        $stats = $this->getStats();
        $pastStats = $this->getPastInstallmentsData();
        $todayStats = $this->getTodayInstallmentsData();
        $futureStats = $this->getFutureInstallmentsData();
        
        $filename = 'تقرير-الأقساط-' . now()->format('Y-m-d-H-i-s') . '.csv';
        
        $headers = array(
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        );

        return response()->stream(function () use ($installments, $stats, $pastStats, $todayStats, $futureStats) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['تقرير شامل للأقساط']);
            fputcsv($handle, ['تاريخ التقرير', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Time-based Statistics
            fputcsv($handle, ['الإحصائيات حسب الوقت']);
            fputcsv($handle, []);

            fputcsv($handle, ['الأقساط السابقة (قبل اليوم)']);
            fputcsv($handle, ['العدد', $pastStats['count']]);
            fputcsv($handle, ['المبلغ الكلي', $pastStats['total_amount']]);
            fputcsv($handle, ['المدفوع', $pastStats['total_paid']]);
            fputcsv($handle, ['المتبقي', $pastStats['total_remaining']]);
            fputcsv($handle, []);

            fputcsv($handle, ['أقساط اليوم']);
            fputcsv($handle, ['العدد', $todayStats['count']]);
            fputcsv($handle, ['المبلغ الكلي', $todayStats['total_amount']]);
            fputcsv($handle, ['المدفوع', $todayStats['total_paid']]);
            fputcsv($handle, ['المتبقي', $todayStats['total_remaining']]);
            fputcsv($handle, []);

            fputcsv($handle, ['الأقساط المستقبلية (بعد اليوم)']);
            fputcsv($handle, ['العدد', $futureStats['count']]);
            fputcsv($handle, ['المبلغ الكلي', $futureStats['total_amount']]);
            fputcsv($handle, ['المدفوع', $futureStats['total_paid']]);
            fputcsv($handle, ['المتبقي', $futureStats['total_remaining']]);
            fputcsv($handle, []);

            fputcsv($handle, ['الإحصائيات العامة']);
            fputcsv($handle, ['إجمالي الأقساط', $stats['total_count']]);
            fputcsv($handle, ['المبلغ الكلي', $stats['total_amount']]);
            fputcsv($handle, ['المدفوع', $stats['total_paid']]);
            fputcsv($handle, ['المتبقي', $stats['total_remaining']]);
            fputcsv($handle, ['المتأخرة', $stats['overdue_count']]);
            fputcsv($handle, ['السداد الجزئي', $stats['partial_count']]);
            fputcsv($handle, []);
            
            fputcsv($handle, ['#', 'الطالب', 'كود الطالب', 'المبلغ', 'المدفوع', 'المتبقي', 'الحالة', 'تاريخ الاستحقاق']);
            
            foreach ($installments as $index => $installment) {
                fputcsv($handle, [
                    $index + 1,
                    $installment->enrollment?->student?->user?->name ?? 'غير محدد',
                    $installment->enrollment?->student?->student_code ?? '-',
                    $installment->amount,
                    $installment->paid_amount,
                    $installment->amount - $installment->paid_amount,
                    $installment->payment_status['status'] ?? '',
                    $installment->due_date?->format('Y-m-d') ?? '-'
                ]);
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="تقرير-الأقساط-' . now()->format('Y-m-d-H-i-s') . '.csv"'
        ]);
    }

    public function exportToPdf()
    {
        $installments = $this->getInstallments();
        $stats = $this->getStats();
        $pastStats = $this->getPastInstallmentsData();
        $todayStats = $this->getTodayInstallmentsData();
        $futureStats = $this->getFutureInstallmentsData();
        
        $html = view('filament.admin.pages.reports.all-installments-report-pdf', [
            'installments' => $installments,
            'stats' => $stats,
            'pastStats' => $pastStats,
            'todayStats' => $todayStats,
            'futureStats' => $futureStats,
        ])->render();
        
        $filename = 'تقرير-الأقساط-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
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
            function() use ($mpdf) {
                echo $mpdf->Output('', 'S');
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}

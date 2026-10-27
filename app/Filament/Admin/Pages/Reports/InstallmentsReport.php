<?php

namespace App\Filament\Admin\Pages\Reports;

use Filament\Pages\Page;
use App\Models\Installment;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InstallmentsReport extends Page
{
    use HasPageShield;

    protected string $view = 'filament.admin.pages.reports.installments-report';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static \UnitEnum|string|null $navigationGroup = 'التقارير المالية';
    
    protected static ?int $navigationSort = 3;

    public $course_id = null;
    public $group_id = null;
    public $student_id = null;
    public $date_from = null;
    public $date_to = null;
    public $time_filter = 'all'; // all, past, today, future

    public static function canAccess(): bool
    {
        return true;
    }

    public function applyFilters(): void
    {
        // Trigger re-render
    }

    public function getCourses()
    {
        return \App\Models\Course::where('tenant_id', auth()->user()->tenant_id)->get();
    }

    public function getGroups()
    {
        return \App\Models\Group::where('tenant_id', auth()->user()->tenant_id)->get();
    }

    public function getStudents()
    {
        return \App\Models\Student::with('user')->where('tenant_id', auth()->user()->tenant_id)->get();
    }

    public static function getNavigationLabel(): string
    {
        return 'تقرير الأقساط المستحقة';
    }

    public function getTitle(): string
    {
        return 'تقرير الأقساط';
    }

    public function getHeading(): string
    {
        return 'تقرير الأقساط المستحقة والقريبة';
    }

    public function getSubheading(): ?string
    {
        return 'متابعة شاملة لحالة الأقساط الدراسية';
    }

    public function getOverdueInstallments()
    {
        $query = Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->overdue();

        // Apply filters
        $this->applyCommonFilters($query);

        // Apply time filter
        if ($this->time_filter === 'past') {
            $query->where('due_date', '<', now()->startOfDay());
        } elseif ($this->time_filter === 'today') {
            $query->whereDate('due_date', '=', now()->toDateString());
        } elseif ($this->time_filter === 'future') {
            $query->where('due_date', '>', now()->endOfDay());
        }

        return $query->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($installment) {
                $installment->days_overdue = now()->startOfDay()->diffInDays($installment->due_date, false) * -1;
                return $installment;
            });
    }

    public function getUpcomingInstallments()
    {
        $today = now()->startOfDay();
        $oneWeekLater = now()->addDays(7)->endOfDay();

        $query = Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->whereIn('status', ['pending', 'partial'])
            ->whereBetween('due_date', [$today, $oneWeekLater]);

        // Apply filters
        $this->applyCommonFilters($query);

        // Apply time filter
        if ($this->time_filter === 'past') {
            $query->where('due_date', '<', now()->startOfDay());
        } elseif ($this->time_filter === 'today') {
            $query->whereDate('due_date', '=', now()->toDateString());
        } elseif ($this->time_filter === 'future') {
            $query->where('due_date', '>', now()->endOfDay());
        }

        return $query->orderBy('due_date', 'asc')
            ->get()
            ->map(function ($installment) {
                $installment->days_until_due = now()->startOfDay()->diffInDays($installment->due_date, false);
                return $installment;
            });
    }

    private function applyCommonFilters($query)
    {
        // Filter by course
        if ($this->course_id) {
            $query->whereHas('enrollment.groups.course', function ($q) {
                $q->where('courses.id', $this->course_id);
            });
        }

        // Filter by group
        if ($this->group_id) {
            $query->whereHas('enrollment.groups', function ($q) {
                $q->where('groups.id', $this->group_id);
            });
        }

        // Filter by student
        if ($this->student_id) {
            $query->whereHas('enrollment.student', function ($q) {
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

        return $query;
    }

    public function getPastInstallments()
    {
        return Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->where('due_date', '<', now()->startOfDay())
            ->where(function ($q) {
                $this->applyCommonFilters($q);
            })
            ->orderBy('due_date', 'desc')
            ->get();
    }

    public function getTodayInstallments()
    {
        return Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->whereDate('due_date', '=', now()->toDateString())
            ->where(function ($q) {
                $this->applyCommonFilters($q);
            })
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getFutureInstallments()
    {
        return Installment::query()
            ->with(['enrollment.student.user', 'enrollment.groups.course'])
            ->where('due_date', '>', now()->endOfDay())
            ->where(function ($q) {
                $this->applyCommonFilters($q);
            })
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function getOverdueStats()
    {
        $installments = $this->getOverdueInstallments();
        
        return [
            'count' => $installments->count(),
            'total_amount' => $installments->sum('remaining_amount'),
            'total_paid' => $installments->sum('paid_amount'),
            'total_due' => $installments->sum('amount'),
        ];
    }

    public function getUpcomingStats()
    {
        $installments = $this->getUpcomingInstallments();
        
        return [
            'count' => $installments->count(),
            'total_amount' => $installments->sum('remaining_amount'),
            'total_paid' => $installments->sum('paid_amount'),
            'total_due' => $installments->sum('amount'),
        ];
    }

    public function getPastStats()
    {
        $installments = $this->getPastInstallments();

        return [
            'count' => $installments->count(),
            'total_amount' => $installments->sum('remaining_amount'),
            'total_paid' => $installments->sum('paid_amount'),
            'total_due' => $installments->sum('amount'),
        ];
    }

    public function getTodayStats()
    {
        $installments = $this->getTodayInstallments();

        return [
            'count' => $installments->count(),
            'total_amount' => $installments->sum('remaining_amount'),
            'total_paid' => $installments->sum('paid_amount'),
            'total_due' => $installments->sum('amount'),
        ];
    }

    public function getFutureStats()
    {
        $installments = $this->getFutureInstallments();

        return [
            'count' => $installments->count(),
            'total_amount' => $installments->sum('remaining_amount'),
            'total_paid' => $installments->sum('paid_amount'),
            'total_due' => $installments->sum('amount'),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'overdueInstallments' => $this->getOverdueInstallments(),
            'upcomingInstallments' => $this->getUpcomingInstallments(),
            'pastInstallments' => $this->getPastInstallments(),
            'todayInstallments' => $this->getTodayInstallments(),
            'futureInstallments' => $this->getFutureInstallments(),
            'overdueStats' => $this->getOverdueStats(),
            'upcomingStats' => $this->getUpcomingStats(),
            'pastStats' => $this->getPastStats(),
            'todayStats' => $this->getTodayStats(),
            'futureStats' => $this->getFutureStats(),
        ];
    }

    public function exportToExcel(): StreamedResponse
    {
        $overdueInstallments = $this->getOverdueInstallments();
        $upcomingInstallments = $this->getUpcomingInstallments();
        $pastInstallments = $this->getPastInstallments();
        $todayInstallments = $this->getTodayInstallments();
        $futureInstallments = $this->getFutureInstallments();
        $overdueStats = $this->getOverdueStats();
        $upcomingStats = $this->getUpcomingStats();
        $pastStats = $this->getPastStats();
        $todayStats = $this->getTodayStats();
        $futureStats = $this->getFutureStats();

        return response()->stream(function () use ($overdueInstallments, $upcomingInstallments, $pastInstallments, $todayInstallments, $futureInstallments, $overdueStats, $upcomingStats, $pastStats, $todayStats, $futureStats) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['تقرير الأقساط المستحقة والقريبة']);
            fputcsv($handle, ['تاريخ التقرير', now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Past Installments Section
            fputcsv($handle, ['الأقساط السابقة (قبل اليوم)']);
            fputcsv($handle, ['إجمالي العدد', $pastStats['count']]);
            fputcsv($handle, ['إجمالي المبلغ المستحق', $pastStats['total_due']]);
            fputcsv($handle, ['إجمالي المدفوع', $pastStats['total_paid']]);
            fputcsv($handle, ['إجمالي المتبقي', $pastStats['total_amount']]);
            fputcsv($handle, []);

            // Today Installments Section
            fputcsv($handle, ['أقساط اليوم']);
            fputcsv($handle, ['إجمالي العدد', $todayStats['count']]);
            fputcsv($handle, ['إجمالي المبلغ المستحق', $todayStats['total_due']]);
            fputcsv($handle, ['إجمالي المدفوع', $todayStats['total_paid']]);
            fputcsv($handle, ['إجمالي المتبقي', $todayStats['total_amount']]);
            fputcsv($handle, []);

            // Future Installments Section
            fputcsv($handle, ['الأقساط المستقبلية (بعد اليوم)']);
            fputcsv($handle, ['إجمالي العدد', $futureStats['count']]);
            fputcsv($handle, ['إجمالي المبلغ المستحق', $futureStats['total_due']]);
            fputcsv($handle, ['إجمالي المدفوع', $futureStats['total_paid']]);
            fputcsv($handle, ['إجمالي المتبقي', $futureStats['total_amount']]);
            fputcsv($handle, []);

            // Overdue Section
            fputcsv($handle, ['الأقساط المتأخرة']);
            fputcsv($handle, ['إجمالي العدد', $overdueStats['count']]);
            fputcsv($handle, ['إجمالي المبلغ', $overdueStats['total_amount']]);
            fputcsv($handle, []);

            fputcsv($handle, ['#', 'الطالب', 'المبلغ', 'المدفوع', 'المتبقي', 'أيام التأخر', 'تاريخ الاستحقاق']);
            foreach ($overdueInstallments as $index => $installment) {
                fputcsv($handle, [
                    $index + 1,
                    $installment->enrollment?->student?->user?->name ?? 'غير محدد',
                    $installment->amount,
                    $installment->paid_amount,
                    $installment->amount - $installment->paid_amount,
                    $installment->days_overdue ?? 0,
                    $installment->due_date?->format('Y-m-d') ?? '-'
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, []);

            // Upcoming Section
            fputcsv($handle, ['الأقساط القريبة (7 أيام)']);
            fputcsv($handle, ['إجمالي العدد', $upcomingStats['count']]);
            fputcsv($handle, ['إجمالي المبلغ', $upcomingStats['total_amount']]);
            fputcsv($handle, []);

            fputcsv($handle, ['#', 'الطالب', 'المبلغ', 'المدفوع', 'المتبقي', 'أيام للاستحقاق', 'تاريخ الاستحقاق']);
            foreach ($upcomingInstallments as $index => $installment) {
                fputcsv($handle, [
                    $index + 1,
                    $installment->enrollment?->student?->user?->name ?? 'غير محدد',
                    $installment->amount,
                    $installment->paid_amount,
                    $installment->amount - $installment->paid_amount,
                    $installment->days_until_due ?? 0,
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
        $overdueInstallments = $this->getOverdueInstallments();
        $upcomingInstallments = $this->getUpcomingInstallments();
        $pastInstallments = $this->getPastInstallments();
        $todayInstallments = $this->getTodayInstallments();
        $futureInstallments = $this->getFutureInstallments();
        $overdueStats = $this->getOverdueStats();
        $upcomingStats = $this->getUpcomingStats();
        $pastStats = $this->getPastStats();
        $todayStats = $this->getTodayStats();
        $futureStats = $this->getFutureStats();

        $html = view('filament.admin.pages.reports.installments-report-pdf', [
            'overdueInstallments' => $overdueInstallments,
            'upcomingInstallments' => $upcomingInstallments,
            'pastInstallments' => $pastInstallments,
            'todayInstallments' => $todayInstallments,
            'futureInstallments' => $futureInstallments,
            'overdueStats' => $overdueStats,
            'upcomingStats' => $upcomingStats,
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

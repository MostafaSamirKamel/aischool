<?php

namespace App\Filament\Admin\Pages;

use BackedEnum;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Exam;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Payment;
use App\Models\SalaryPayment;
use App\Models\Tenant;
use App\Models\Classroom;
use App\Models\Student;
use Filament\Pages\Page;
use App\Models\Attendance;
use App\Models\Instructor;
use App\Models\SessionSchedule;
use App\Models\EnrollmentCourse;
use App\Models\StudentPayment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class AdvancedReports extends Page
{
    use HasPageShield;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.admin.pages.advanced-reports';
    
    protected static ?string $navigationLabel = null;
    
    protected static ?string $title = null;
    
    protected static ?int $navigationSort = 100;

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public static function getNavigationGroup(): ?string
    {
        return __('filament/admin/advanced_reports.navigation_group');
    }

    public function mount(): void
    {
        abort_unless(Auth::user()->hasAnyRole(['super_admin', 'branch_manager']), 403);

        $this->dateFrom = now()->subMonths(6)->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updatedDateFrom(): void
    {
        $this->normalizeDateRange();
    }

    public function updatedDateTo(): void
    {
        $this->normalizeDateRange();
    }

    public function resetDateFilters(): void
    {
        $this->dateFrom = now()->subMonths(6)->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    protected function normalizeDateRange(): void
    {
        if (!$this->dateFrom || !$this->dateTo) {
            return;
        }

        $from = Carbon::parse($this->dateFrom);
        $to = Carbon::parse($this->dateTo);

        if ($from->greaterThan($to)) {
            $this->dateFrom = $to->toDateString();
            $this->dateTo = $from->toDateString();
        }
    }

    protected function getDateRange(): array
    {
        $from = $this->dateFrom
            ? Carbon::parse($this->dateFrom)->startOfDay()
            : now()->subMonths(6)->startOfMonth()->startOfDay();

        $to = $this->dateTo
            ? Carbon::parse($this->dateTo)->endOfDay()
            : now()->endOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    protected function getMonthBuckets(): array
    {
        [$from, $to] = $this->getDateRange();

        $months = [];
        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();

        while ($cursor->lessThanOrEqualTo($end)) {
            $months[] = $cursor->copy();
            $cursor->addMonth();
        }

        return $months;
    }

    public function getStats(): array
    {
        $user = Auth::user();
        $isBranchManager = $user->hasRole('branch_manager');
        $tenantId = $isBranchManager ? $user->tenant_id : null;
        [$from, $to] = $this->getDateRange();

        // إجمالي الطلاب
        $totalStudents = Student::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->count();

        // إجمالي المدرسين
        $totalInstructors = Instructor::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->count();

        // إجمالي المجموعات
        $totalGroups = Group::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->count();

        // المجموعات النشطة
        $activeGroups = Group::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->count();

        // نسبة الحضور (آخر 30 يوم)
        $attendanceRate = Attendance::query()
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->whereHas('student', fn($sq) => $sq->where('tenant_id', $tenantId));
            })
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where('status', 'present')
            ->count();

        $totalAttendance = Attendance::query()
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->whereHas('student', fn($sq) => $sq->where('tenant_id', $tenantId));
            })
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->count();

        $attendancePercentage = $totalAttendance > 0 
            ? round(($attendanceRate / $totalAttendance) * 100, 1) 
            : 0;

        // إجمالي الإيرادات (آخر 30 يوم)
        $totalRevenue = StudentPayment::query()
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->whereHas('student', fn($sq) => $sq->where('tenant_id', $tenantId));
            })
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->where('status', 'completed')
            ->sum('total_amount');

        // عدد الامتحانات (آخر 30 يوم)
        $totalExams = Exam::query()
            ->when($tenantId, function ($q) use ($tenantId) {
                $q->whereHas('group', fn($gq) => $gq->where('tenant_id', $tenantId));
            })
            ->whereBetween('exam_date', [$from->toDateString(), $to->toDateString()])
            ->count();

        return [
            'totalStudents' => $totalStudents,
            'totalInstructors' => $totalInstructors,
            'totalGroups' => $totalGroups,
            'activeGroups' => $activeGroups,
            'attendancePercentage' => $attendancePercentage,
            'totalRevenue' => number_format($totalRevenue, 2),
            'totalExams' => $totalExams,
        ];
    }

    public function getAttendanceByMonth(): array
    {
        $user = Auth::user();
        $isBranchManager = $user->hasRole('branch_manager');
        $tenantId = $isBranchManager ? $user->tenant_id : null;
        $months = $this->getMonthBuckets();

        $data = [];
        foreach ($months as $month) {
            $monthName = $month->locale('ar')->format('F');
            
            $present = Attendance::query()
                ->when($tenantId, function ($q) use ($tenantId) {
                    $q->whereHas('student', fn($sq) => $sq->where('tenant_id', $tenantId));
                })
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->where('status', 'present')
                ->count();

            $absent = Attendance::query()
                ->when($tenantId, function ($q) use ($tenantId) {
                    $q->whereHas('student', fn($sq) => $sq->where('tenant_id', $tenantId));
                })
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->where('status', 'absent')
                ->count();

            $late = Attendance::query()
                ->when($tenantId, function ($q) use ($tenantId) {
                    $q->whereHas('student', fn($sq) => $sq->where('tenant_id', $tenantId));
                })
                ->whereYear('date', $month->year)
                ->whereMonth('date', $month->month)
                ->where('status', 'late')
                ->count();

            $data[] = [
                'month' => $monthName,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
            ];
        }

        return $data;
    }

    public function getRevenueByMonth(): array
    {
        $user = Auth::user();
        $isBranchManager = $user->hasRole('branch_manager');
        $tenantId = $isBranchManager ? $user->tenant_id : null;
        $months = $this->getMonthBuckets();

        $data = [];
        foreach ($months as $month) {
            $monthName = $month->locale('ar')->format('F');
            
            $revenue = StudentPayment::query()
                ->when($tenantId, function ($q) use ($tenantId) {
                    $q->whereHas('student', fn($sq) => $sq->where('tenant_id', $tenantId));
                })
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->where('status', 'completed')
                ->sum('total_amount');

            $data[] = [
                'month' => $monthName,
                'revenue' => (float) $revenue,
            ];
        }

        return $data;
    }

    public function getTopInstructors(): array
    {
        $user = Auth::user();
        $isBranchManager = $user->hasRole('branch_manager');
        $tenantId = $isBranchManager ? $user->tenant_id : null;

        return Instructor::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->withCount('groups')
            ->with([
                'groups' => function ($query) {
                    $query->withCount('enrollments');
                }
            ])
            ->orderBy('groups_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function($instructor) {
                return [
                    'name' => $instructor->user->name ?? 'غير محدد',
                    'groups_count' => $instructor->groups_count,
                    'students_count' => $instructor->groups->sum('enrollments_count'),
                ];
            })
            ->toArray();
    }

    public function getGroupsDistribution(): array
    {
        $user = Auth::user();
        $isBranchManager = $user->hasRole('branch_manager');
        $tenantId = $isBranchManager ? $user->tenant_id : null;

        $active = Group::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'active')
            ->count();

        $completed = Group::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->count();

        $pending = Group::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'pending')
            ->count();

        $cancelled = Group::query()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->where('status', 'cancelled')
            ->count();

        return [
            ['status' => 'نشطة', 'count' => $active, 'color' => '#10b981'],
            ['status' => 'مكتملة', 'count' => $completed, 'color' => '#6366f1'],
            ['status' => 'قيد الانتظار', 'count' => $pending, 'color' => '#f59e0b'],
            ['status' => 'ملغية', 'count' => $cancelled, 'color' => '#ef4444'],
        ];
    }

    public function getBranchesComparison(): array
    {
        $user = Auth::user();
        [$from, $to] = $this->getDateRange();
        
        // فقط super_admin يرى مقارنة الفروع
        if (!$user->hasRole('super_admin')) {
            return [];
        }

        return Tenant::query()
            ->get()
            ->map(function ($tenant) use ($from, $to){
                $studentsCount = Student::where('tenant_id', $tenant->id)->count();
                $instructorsCount = Instructor::where('tenant_id', $tenant->id)->count();
                $groupsCount = Group::where('tenant_id', $tenant->id)->count();

                $revenue = StudentPayment::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', 'completed')
                    ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
                    ->sum('total_amount');

                return [
                    'name' => $tenant->name,
                    'students' => $studentsCount,
                    'instructors' => $instructorsCount,
                    'groups' => $groupsCount,
                    'revenue' => (float) $revenue,
                ];
            })
            ->toArray();
    }

    public function getProfitByMonth(): array
    {
        $tenantId = $this->getTenantIdForCurrentUser();
        $months = $this->getMonthBuckets();

        $data = [];
        foreach ($months as $month) {

            $profit = Payment::query()
                ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                ->where('status', 'completed')
                ->whereYear('payment_date', $month->year)
                ->whereMonth('payment_date', $month->month)
                ->sum('amount');

            $data[] = [
                'month' => $month->locale('ar')->format('F'),
                'profit' => round((float) $profit, 2),
            ];
        }

        return $data;
    }

    public function getProfitByCourse(): array
    {
        $distribution = $this->getDistributedProfitData();

        return collect($distribution['courses'])
            ->sortByDesc('profit')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function getProfitByGroup(): array
    {
        $distribution = $this->getDistributedProfitData();

        return collect($distribution['groups'])
            ->sortByDesc('profit')
            ->take(10)
            ->values()
            ->toArray();
    }

    public function getSubscribersByCourse(): array
    {
        $tenantId = $this->getTenantIdForCurrentUser();

        return EnrollmentCourse::query()
            ->with('course:id,name')
            ->when($tenantId, function ($query) use ($tenantId) {
                $query->whereHas('enrollment', fn ($enrollmentQuery) => $enrollmentQuery->where('tenant_id', $tenantId));
            })
            ->selectRaw('course_id, COUNT(DISTINCT enrollment_id) as subscribers_count')
            ->groupBy('course_id')
            ->orderByDesc('subscribers_count')
            ->get()
            ->map(fn ($item) => [
                'course' => $item->course?->name ?? 'غير محدد',
                'count' => (int) $item->subscribers_count,
            ])
            ->toArray();
    }

    public function getClassroomsReport(): array
    {
        $tenantId = $this->getTenantIdForCurrentUser();
        $now = now();
        $currentDay = strtolower($now->englishDayOfWeek);
        $currentTime = $now->format('H:i:s');

        $classrooms = Classroom::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->with([
                'groups' => fn ($query) => $query
                    ->select('id', 'name', 'course_id', 'classroom_id', 'schedule', 'status')
                    ->whereIn('status', ['active', 'pending'])
                    ->with('course:id,name'),
            ])
            ->withCount([
                'groups as active_groups_count' => fn ($query) => $query->where('status', 'active'),
                'groups as total_groups_count',
            ])
            ->orderBy('name')
            ->get();

        $items = $classrooms
            ->map(function ($classroom) use ($currentDay, $currentTime) {
                $busySlots = collect();
                $groupsSchedule = [];

                foreach ($classroom->groups as $group) {
                    if (!is_array($group->schedule)) {
                        continue;
                    }

                    foreach ($group->schedule as $slot) {
                        $day = $slot['day'] ?? null;
                        $startTime = $slot['start_time'] ?? null;
                        $endTime = $slot['end_time'] ?? null;

                        if (!$day || !$startTime || !$endTime) {
                            continue;
                        }

                        $busySlots->push([
                            'day' => $day,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                        ]);

                        $groupsSchedule[] = sprintf(
                            '%s (%s): %s %s - %s',
                            $group->name,
                            $group->course?->name ?? 'غير محدد',
                            $this->formatDay($day),
                            $this->formatTime($startTime),
                            $this->formatTime($endTime)
                        );
                    }
                }

                $isOccupiedNow = $busySlots->contains(function (array $slot) use ($currentDay, $currentTime) {
                    return $slot['day'] === $currentDay
                        && $slot['start_time'] <= $currentTime
                        && $slot['end_time'] >= $currentTime;
                });

                $freeSlots = $this->buildClassroomFreeSlots($busySlots);

                return [
                    'name' => $classroom->name,
                    'status' => $classroom->status,
                    'active_groups' => (int) $classroom->active_groups_count,
                    'total_groups' => (int) $classroom->total_groups_count,
                    'is_occupied_now' => $isOccupiedNow,
                    'is_available_now' => $classroom->status === 'active' && !$isOccupiedNow,
                    'groups_schedule' => array_values(array_unique($groupsSchedule)),
                    'free_slots' => $freeSlots,
                    'busy_slots_count' => $busySlots->count(),
                ];
            })
            ->values();

        return [
            'summary' => [
                'total' => $items->count(),
                'active' => $items->where('status', 'active')->count(),
                'inactive' => $items->where('status', '!=', 'active')->count(),
                'available_now' => $items->where('is_available_now', true)->count(),
                'occupied_now' => $items->where('is_occupied_now', true)->count(),
                'with_schedule' => $items->where('busy_slots_count', '>', 0)->count(),
            ],
            'items' => $items->toArray(),
        ];
    }

    public function getFinancialOverview(): array
    {
        $tenantId = $this->getTenantIdForCurrentUser();
        [$from, $to] = $this->getDateRange();

        $totalCollected = (float) Payment::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $totalExpenses = (float) Expense::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $totalSalaries = (float) SalaryPayment::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->where('status', 'paid')
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        $totalOperatingCost = $totalExpenses + $totalSalaries;

        return [
            'total_collected' => round($totalCollected, 2),
            'total_expenses' => round($totalExpenses, 2),
            'total_salaries' => round($totalSalaries, 2),
            'total_operating_cost' => round($totalOperatingCost, 2),
            'total_net' => round($totalCollected - $totalOperatingCost, 2),
        ];
    }

    protected function getDistributedProfitData(): array
    {
        $tenantId = $this->getTenantIdForCurrentUser();
        [$from, $to] = $this->getDateRange();

        $payments = Payment::query()
            ->where('status', 'completed')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->with([
                'enrollment.enrollmentCourses.course:id,name',
                'enrollment.enrollmentCourses.group:id,name',
            ])
            ->get();

        $courses = [];
        $groups = [];

        foreach ($payments as $payment) {
            $enrollmentCourses = $payment->enrollment?->enrollmentCourses;

            if (!$enrollmentCourses || $enrollmentCourses->isEmpty()) {
                continue;
            }

            $pricedCourses = $enrollmentCourses->filter(
                fn ($enrollmentCourse) => (float) $enrollmentCourse->course_price > 0
            );

            $totalCoursesPrice = (float) $pricedCourses->sum('course_price');

            if ($totalCoursesPrice <= 0) {
                continue;
            }

            foreach ($pricedCourses as $enrollmentCourse) {
                $share = ((float) $enrollmentCourse->course_price / $totalCoursesPrice) * (float) $payment->amount;

                if ($enrollmentCourse->course_id) {
                    $courseKey = (string) $enrollmentCourse->course_id;
                    $courses[$courseKey] = [
                        'name' => $enrollmentCourse->course?->name ?? 'غير محدد',
                        'gross' => round((float) (($courses[$courseKey]['gross'] ?? 0) + $share), 2),
                    ];
                }

                if ($enrollmentCourse->group_id) {
                    $groupKey = (string) $enrollmentCourse->group_id;
                    $groups[$groupKey] = [
                        'name' => $enrollmentCourse->group?->name ?? 'غير محدد',
                        'gross' => round((float) (($groups[$groupKey]['gross'] ?? 0) + $share), 2),
                    ];
                }
            }
        }

        $financial = $this->getFinancialOverview();

        $courses = $this->applyNetProfitDistribution(array_values($courses), (float) $financial['total_operating_cost']);
        $groups = $this->applyNetProfitDistribution(array_values($groups), (float) $financial['total_operating_cost']);

        return [
            'courses' => $courses,
            'groups' => $groups,
        ];
    }

    protected function applyNetProfitDistribution(array $items, float $totalOperatingCost): array
    {
        $grossTotal = collect($items)->sum(fn ($item) => (float) ($item['gross'] ?? 0));

        return collect($items)
            ->map(function (array $item) use ($grossTotal, $totalOperatingCost) {
                $gross = (float) ($item['gross'] ?? 0);
                $expenseShare = $grossTotal > 0 ? ($gross / $grossTotal) * $totalOperatingCost : 0;
                $net = $gross - $expenseShare;

                return [
                    'name' => $item['name'] ?? 'غير محدد',
                    'gross' => round($gross, 2),
                    'expense_share' => round($expenseShare, 2),
                    'net' => round($net, 2),
                    'profit' => round($net, 2),
                ];
            })
            ->toArray();
    }

    protected function buildClassroomFreeSlots(Collection $busySlots): array
    {
        $workingStart = '08:00:00';
        $workingEnd = '22:00:00';
        $days = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

        $freeSlots = [];

        foreach ($days as $day) {
            $daySlots = $busySlots
                ->where('day', $day)
                ->sortBy('start_time')
                ->values();

            if ($daySlots->isEmpty()) {
                $freeSlots[] = sprintf('%s: %s - %s', $this->formatDay($day), $this->formatTime($workingStart), $this->formatTime($workingEnd));
                continue;
            }

            $cursor = $workingStart;

            foreach ($daySlots as $slot) {
                $slotStart = $slot['start_time'];
                $slotEnd = $slot['end_time'];

                if ($slotStart > $cursor) {
                    $freeSlots[] = sprintf('%s: %s - %s', $this->formatDay($day), $this->formatTime($cursor), $this->formatTime($slotStart));
                }

                if ($slotEnd > $cursor) {
                    $cursor = $slotEnd;
                }
            }

            if ($cursor < $workingEnd) {
                $freeSlots[] = sprintf('%s: %s - %s', $this->formatDay($day), $this->formatTime($cursor), $this->formatTime($workingEnd));
            }
        }

        return array_slice($freeSlots, 0, 12);
    }

    protected function formatTime(string $time): string
    {
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        return \Carbon\Carbon::createFromFormat('H:i:s', $time)->format('h:i A');
    }

    protected function getTenantIdForCurrentUser(): ?int
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            return null;
        }

        if ($user->hasRole('branch_manager')) {
            return $user->tenant_id;
        }

        return $user->tenant_id;
    }

    public function exportReport(string $type, string $format = 'csv')
    {
        $rows = $this->getReportRows($type);
        $title = $this->getReportTitle($type);
        $periodLabel = $this->getExportPeriodLabel();
        $periodSlug = $this->getExportPeriodSlug();

        if ($format === 'pdf') {
            $headers = !empty($rows) ? array_keys($rows[0]) : [];

            $html = view('filament.admin.pages.reports.simple-table-pdf', [
                'title' => $title,
                'headers' => $headers,
                'rows' => $rows,
                'period' => $periodLabel,
            ])->render();

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

            return Response::streamDownload(
                fn () => print($pdf->output()),
                Str::slug($title) . '-' . $periodSlug . '-' . now()->format('Ymd-His') . '.pdf'
            );
        }

        $headers = !empty($rows) ? array_keys($rows[0]) : [];
        $filename = Str::slug($title) . '-' . $periodSlug . '-' . now()->format('Ymd-His') . '.csv';

        return Response::streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');

            // UTF-8 BOM for proper Arabic display in Excel.
            fwrite($output, "\xEF\xBB\xBF");

            if (!empty($headers)) {
                fputcsv($output, $headers);
            }

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename);
    }

    protected function getExportPeriodLabel(): string
    {
        [$from, $to] = $this->getDateRange();

        return 'الفترة: ' . $from->format('Y-m-d') . ' إلى ' . $to->format('Y-m-d');
    }

    protected function getExportPeriodSlug(): string
    {
        [$from, $to] = $this->getDateRange();

        return $from->format('Ymd') . '-to-' . $to->format('Ymd');
    }

    protected function getReportRows(string $type): array
    {
        return match ($type) {
            'profit-month' => collect($this->getProfitByMonth())
                ->map(fn ($item) => [
                    'الشهر' => $item['month'],
                    'الربح' => $item['profit'],
                ])
                ->toArray(),
            'profit-course' => collect($this->getProfitByCourse())
                ->map(fn ($item) => [
                    'الكورس' => $item['name'],
                    'إجمالي المحصل' => $item['gross'],
                    'حصة المصروفات' => $item['expense_share'],
                    'صافي الربح' => $item['net'],
                ])
                ->toArray(),
            'profit-group' => collect($this->getProfitByGroup())
                ->map(fn ($item) => [
                    'المجموعة' => $item['name'],
                    'إجمالي المحصل' => $item['gross'],
                    'حصة المصروفات' => $item['expense_share'],
                    'صافي الربح' => $item['net'],
                ])
                ->toArray(),
            'subscribers-course' => collect($this->getSubscribersByCourse())
                ->map(fn ($item) => [
                    'الكورس' => $item['course'],
                    'عدد المشتركين' => $item['count'],
                ])
                ->toArray(),
            'classrooms' => collect($this->getClassroomsReport()['items'])
                ->map(fn ($item) => [
                    'القاعة' => $item['name'],
                    'حالة القاعة' => $item['status'] === 'active' ? 'نشطة' : 'غير نشطة',
                    'حالة الآن' => $item['is_occupied_now'] ? 'مشغولة' : ($item['is_available_now'] ? 'متاحة' : 'غير متاحة'),
                    'مجموعات نشطة' => $item['active_groups'],
                    'إجمالي المجموعات' => $item['total_groups'],
                    'الجروبات ومواعيدها' => implode(' | ', $item['groups_schedule'] ?? []),
                    'الأوقات الفارغة' => implode(' | ', $item['free_slots'] ?? []),
                ])
                ->toArray(),
            default => [],
        };
    }

    protected function getReportTitle(string $type): string
    {
        return match ($type) {
            'profit-month' => 'تقرير الأرباح حسب الشهر',
            'profit-course' => 'تقرير الأرباح حسب الكورس',
            'profit-group' => 'تقرير الأرباح حسب المجموعة',
            'subscribers-course' => 'تقرير عدد المشتركين بكل كورس',
            'classrooms' => 'تقرير القاعات والقاعات المتاحة',
            default => 'تقرير',
        };
    }

    public function getTitle(): string
    {
        return __('filament/admin/advanced_reports.title');
    }
    public static function getNavigationLabel(): string
    {
        return __('filament/admin/advanced_reports.navigation_label');
    }
// ضف الكود ده في آخر الملف قبل القوس } الأخير

protected function formatDay(string $day): string
{
    $days = [
        'saturday'  => 'السبت',
        'sunday'    => 'الأحد',
        'monday'    => 'الإثنين',
        'tuesday'   => 'الثلاثاء',
        'wednesday' => 'الأربعاء',
        'thursday'  => 'الخميس',
        'friday'    => 'الجمعة',
    ];

    // لو اليوم مبعوت بالإنجليزي هيترجمه، لو مش موجود في المصفوفة هيرجعه زي ما هو
    return $days[strtolower($day)] ?? $day;
}


}

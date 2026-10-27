<?php

namespace App\Filament\Admin\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\EnrollmentCourse;
use App\Models\ExamAttempt;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class StudentPortfolioReport extends Page
{
    use HasPageShield;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'التقارير';

    protected static ?int $navigationSort = 81;

    protected string $view = 'filament.admin.pages.student-portfolio-report';

    public ?int $studentId = null;
    public ?int $tenantFilter = null;
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public array $portfolio = [
        'student_name' => null,
        'overview' => [],
        'groups' => [],
        'payments' => [],
        'attendance' => [],
        'exams' => [],
        'assignments' => [],
    ];

    public function mount(): void
    {
        $this->dateFrom = now()->subMonths(6)->toDateString();
        $this->dateTo = now()->toDateString();

        if (!$this->canSelectAnyTenant()) {
            $this->tenantFilter = $this->getTenantIdForCurrentUser();
        }

        $firstStudentId = array_key_first($this->getStudentsOptions());

        if ($firstStudentId) {
            $this->studentId = (int) $firstStudentId;
            $this->loadPortfolio();
        }
    }

    public function updatedStudentId(): void
    {
        $this->loadPortfolio();
    }

    public function updatedTenantFilter(): void
    {
        $firstStudentId = array_key_first($this->getStudentsOptions());
        $this->studentId = $firstStudentId ? (int) $firstStudentId : null;
        $this->loadPortfolio();
    }

    public function updatedDateFrom(): void
    {
        $this->loadPortfolio();
    }

    public function updatedDateTo(): void
    {
        $this->loadPortfolio();
    }

    public function getStudentsOptions(): array
    {
        $tenantId = $this->resolveTenantFilter();

        return Student::query()
            ->with('user:id,name')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->orderBy('id', 'desc')
            ->get()
            ->mapWithKeys(function ($student) {
                $name = $student->user?->name ?? ('طالب #' . $student->id);

                return [$student->id => $name];
            })
            ->toArray();
    }

    public function getTenantOptions(): array
    {
        if (!$this->canSelectAnyTenant()) {
            return [];
        }

        return Tenant::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function loadPortfolio(): void
    {
        if (!$this->studentId) {
            $this->portfolio = [
                'student_name' => null,
                'overview' => [],
                'groups' => [],
                'payments' => [],
                'attendance' => [],
                'exams' => [],
                'assignments' => [],
            ];

            return;
        }

        $tenantId = $this->resolveTenantFilter();
        $student = Student::query()
            ->with('user:id,name')
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->find($this->studentId);

        if (!$student) {
            $this->portfolio = [
                'student_name' => null,
                'overview' => [],
                'groups' => [],
                'payments' => [],
                'attendance' => [],
                'exams' => [],
                'assignments' => [],
            ];

            return;
        }

        $attendanceQuery = Attendance::query()->where('student_id', $student->id);
        $this->applyDateRange($attendanceQuery, 'date');

        $attendanceTotal = (clone $attendanceQuery)->count();
        $attendancePresent = (clone $attendanceQuery)->where('status', 'present')->count();

        $attendanceRate = $attendanceTotal > 0
            ? round(($attendancePresent / $attendanceTotal) * 100, 1)
            : 0;

        $totalPaid = Payment::query()
            ->where('status', 'completed')
            ->whereHas('enrollment', fn ($query) => $query->where('student_id', $student->id))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('payment_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('payment_date', '<=', $this->dateTo))
            ->sum('amount');

        $groupsQuery = EnrollmentCourse::query()
            ->whereHas('enrollment', fn ($query) => $query->where('student_id', $student->id))
            ->whereNotNull('group_id')
            ->with(['group.course'])
            ->latest('updated_at');
        $this->applyDateRange($groupsQuery, 'updated_at');

        $groups = $groupsQuery
            ->get()
            ->unique('group_id')
            ->take(20)
            ->map(fn ($item) => [
                'group_name' => $item->group?->name ?? 'غير محدد',
                'course_name' => $item->group?->course?->name ?? 'غير محدد',
                'status' => $item->status,
                'progress' => $item->total_sessions > 0
                    ? round(((float) $item->current_session / (float) $item->total_sessions) * 100, 1)
                    : 0,
            ])
            ->values()
            ->toArray();

        $paymentsQuery = Payment::query()
            ->whereHas('enrollment', fn ($query) => $query->where('student_id', $student->id))
            ->latest('payment_date');
        $this->applyDateRange($paymentsQuery, 'payment_date');

        $payments = $paymentsQuery
            ->with('vault')
            ->limit(20)
            ->get()
            ->map(fn ($payment) => [
                'date' => optional($payment->payment_date)->format('Y-m-d'),
                'amount' => (float) $payment->amount,
                'status' => $payment->status,
                'vault' => $payment->vault?->name ?? 'غير محدد',
                'method' => $payment->vault?->name ?? $payment->payment_method ?? 'نقدي',
            ])
            ->toArray();

        $attendanceListQuery = Attendance::query()
            ->where('student_id', $student->id)
            ->with('group:id,name')
            ->latest('date');
        $this->applyDateRange($attendanceListQuery, 'date');

        $attendance = $attendanceListQuery
            ->limit(20)
            ->get()
            ->map(fn ($record) => [
                'date' => optional($record->date)->format('Y-m-d'),
                'group' => $record->group?->name ?? 'غير محدد',
                'status' => $record->status,
            ])
            ->toArray();

        $examsQuery = ExamAttempt::query()
            ->where('student_id', $student->id)
            ->with('exam:id,title,total_marks')
            ->latest('completed_at');
        $this->applyDateRange($examsQuery, 'completed_at');

        $exams = $examsQuery
            ->limit(20)
            ->get()
            ->map(function ($attempt) {
                $totalMarks = (float) ($attempt->exam?->total_marks ?? 0);
                $score = (float) ($attempt->score ?? 0);

                return [
                    'exam' => $attempt->exam?->title ?? 'غير محدد',
                    'score' => $score,
                    'total' => $totalMarks,
                    'percentage' => $totalMarks > 0 ? round(($score / $totalMarks) * 100, 1) : 0,
                    'passed' => (bool) $attempt->passed,
                ];
            })
            ->toArray();

        $assignmentsQuery = AssignmentSubmission::query()
            ->where('student_id', $student->id)
            ->with('assignment:id,title,max_score')
            ->latest('submitted_at');
        $this->applyDateRange($assignmentsQuery, 'submitted_at');

        $assignments = $assignmentsQuery
            ->limit(20)
            ->get()
            ->map(fn ($submission) => [
                'assignment' => $submission->assignment?->title ?? 'غير محدد',
                'submitted_at' => optional($submission->submitted_at)->format('Y-m-d H:i'),
                'status' => $submission->status,
                'score' => $submission->score,
                'max_score' => $submission->assignment?->max_score,
            ])
            ->toArray();

        $this->portfolio = [
            'student_name' => $student->user?->name ?? ('طالب #' . $student->id),
            'overview' => [
                'groups_count' => count($groups),
                'attendance_rate' => $attendanceRate,
                'exams_count' => count($exams),
                'assignments_count' => count($assignments),
                'total_paid' => round((float) $totalPaid, 2),
            ],
            'groups' => $groups,
            'payments' => $payments,
            'attendance' => $attendance,
            'exams' => $exams,
            'assignments' => $assignments,
        ];
    }

    protected function getTenantIdForCurrentUser(): ?int
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return null;
        }

        return $user?->tenant_id;
    }

    protected function canSelectAnyTenant(): bool
    {
        return (bool) auth()->user()?->hasRole('super_admin');
    }

    protected function resolveTenantFilter(): ?int
    {
        if ($this->canSelectAnyTenant()) {
            return $this->tenantFilter;
        }

        return $this->getTenantIdForCurrentUser();
    }

    protected function applyDateRange(Builder $query, string $column): void
    {
        if ($this->dateFrom) {
            $query->whereDate($column, '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate($column, '<=', $this->dateTo);
        }
    }

    public function exportPortfolio(string $format = 'csv')
    {
        if (!$this->studentId) {
            Notification::make()
                ->title('يجب اختيار طالب أولاً')
                ->danger()
                ->send();

            return null;
        }

        $this->loadPortfolio();

        if (empty($this->portfolio['student_name'])) {
            Notification::make()
                ->title('تعذر تحميل بيانات الطالب')
                ->danger()
                ->send();

            return null;
        }

        $baseFileName = Str::slug('portfolio-' . $this->portfolio['student_name']) . '-' . now()->format('Ymd-His');

        if ($format === 'pdf') {
            $html = view('filament.admin.pages.reports.student-portfolio-pdf', [
                'portfolio' => $this->portfolio,
                'dateFrom' => $this->dateFrom,
                'dateTo' => $this->dateTo,
            ])->render();

            $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

            return Response::streamDownload(
                fn () => print($pdf->output()),
                $baseFileName . '.pdf'
            );
        }

        $rows = $this->buildPortfolioExportRows();
        $headers = !empty($rows) ? array_keys($rows[0]) : ['القسم', 'العنوان', 'القيمة', 'تفاصيل'];

        return Response::streamDownload(function () use ($headers, $rows) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, $headers);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, $baseFileName . '.csv');
    }

    protected function buildPortfolioExportRows(): array
    {
        $rows = [];

        $rows[] = [
            'القسم' => 'ملخص',
            'العنوان' => 'اسم الطالب',
            'القيمة' => $this->portfolio['student_name'],
            'تفاصيل' => 'الفترة: ' . ($this->dateFrom ?? '-') . ' إلى ' . ($this->dateTo ?? '-'),
        ];

        foreach ($this->portfolio['overview'] as $key => $value) {
            $rows[] = [
                'القسم' => 'ملخص',
                'العنوان' => $key,
                'القيمة' => $value,
                'تفاصيل' => '',
            ];
        }

        foreach ($this->portfolio['groups'] as $group) {
            $rows[] = [
                'القسم' => 'المجموعات',
                'العنوان' => $group['group_name'] ?? '',
                'القيمة' => $group['status'] ?? '',
                'تفاصيل' => 'الكورس: ' . ($group['course_name'] ?? '') . ' | التقدم: ' . (($group['progress'] ?? 0) . '%'),
            ];
        }

        foreach ($this->portfolio['payments'] as $payment) {
            $rows[] = [
                'القسم' => 'المدفوعات',
                'العنوان' => $payment['date'] ?? '',
                'القيمة' => $payment['amount'] ?? '',
                'تفاصيل' => 'الحالة: ' . ($payment['status'] ?? '') . ' | الطريقة: ' . ($payment['method'] ?? ''),
            ];
        }

        foreach ($this->portfolio['attendance'] as $attendance) {
            $rows[] = [
                'القسم' => 'الحضور',
                'العنوان' => $attendance['date'] ?? '',
                'القيمة' => $attendance['status'] ?? '',
                'تفاصيل' => 'المجموعة: ' . ($attendance['group'] ?? ''),
            ];
        }

        foreach ($this->portfolio['exams'] as $exam) {
            $rows[] = [
                'القسم' => 'الامتحانات',
                'العنوان' => $exam['exam'] ?? '',
                'القيمة' => ($exam['score'] ?? 0) . '/' . ($exam['total'] ?? 0),
                'تفاصيل' => 'النسبة: ' . (($exam['percentage'] ?? 0) . '%'),
            ];
        }

        foreach ($this->portfolio['assignments'] as $assignment) {
            $rows[] = [
                'القسم' => 'الواجبات',
                'العنوان' => $assignment['assignment'] ?? '',
                'القيمة' => $assignment['status'] ?? '',
                'تفاصيل' => 'التاريخ: ' . ($assignment['submitted_at'] ?? '') . ' | الدرجة: ' . (($assignment['score'] ?? '-') . '/' . ($assignment['max_score'] ?? '-')),
            ];
        }

        return $rows;
    }

    public function getTitle(): string
    {
        return 'بورتفوليو الطالب';
    }

    public static function getNavigationLabel(): string
    {
        return 'بورتفوليو الطالب';
    }
}

<?php

namespace App\Filament\Admin\Pages;

use App\Models\Course;
use App\Models\Group;
use App\Models\ReportSetting;
use App\Models\SavedReport;
use App\Models\ScheduledReport;
use App\Models\Tenant;
use App\Models\Vault;
use App\Services\Reporting\ReportingCenterService;
use App\Services\Reporting\ReportingExportService;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportingCenterPage extends Page
{

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected string $view = 'filament.admin.pages.reporting-center-page';
    protected static ?string $navigationLabel = 'مركز التقارير الموحد';
    protected static ?string $title = 'مركز التقارير والاستخبارات التشغيلية والمالية';
    protected static ?int $navigationSort = 1;
    protected static string|\UnitEnum|null $navigationGroup = 'التقارير';

    // Active Section / Tab
    public string $activeSection = 'overview';

    // Global Filter State
    public string $periodPreset = 'this_month';
    public ?string $dateFrom = null;
    public ?string $dateTo = null;
    public ?string $asOfDate = null;
    public ?int $selectedBranchId = null;
    public ?int $selectedCourseId = null;
    public ?int $selectedGroupId = null;
    public ?int $selectedVaultId = null;

    // Drill-Down Modal State
    public bool $showDrillDownModal = false;
    public string $drillDownTitle = '';
    public array $drillDownHeaders = [];
    public array $drillDownRows = [];

    // Save Report Modal State
    public bool $showSaveReportModal = false;
    public string $savedReportName = '';

    // Data Cache for Active View
    public array $reportData = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Parent, Student, and regular Instructor roles do not have admin reporting center access
        if ($user->hasRole('student') || $user->hasRole('parent')) {
            return false;
        }

        return $user->is_super_admin
            || $user->hasAnyRole(['super_admin', 'admin', 'branch_manager', 'accountant', 'registration', 'supervisor'])
            || $user->can('view_reporting_center');
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        // Initialize branch isolation
        if (!$user->is_super_admin && !$user->hasRole('super_admin') && $user->tenant_id) {
            $this->selectedBranchId = (int) $user->tenant_id;
        }

        // Handle URL query parameters (Contextual Shortcuts)
        if (request()->has('section')) {
            $this->activeSection = request()->query('section');
        }
        if (request()->has('branch_id')) {
            $requestedBranch = (int) request()->query('branch_id');
            if ($user->is_super_admin || $user->hasRole('super_admin') || (int) $user->tenant_id === $requestedBranch) {
                $this->selectedBranchId = $requestedBranch;
            }
        }
        if (request()->has('course_id')) {
            $this->selectedCourseId = (int) request()->query('course_id');
        }
        if (request()->has('group_id')) {
            $this->selectedGroupId = (int) request()->query('group_id');
        }
        if (request()->has('vault_id')) {
            $this->selectedVaultId = (int) request()->query('vault_id');
        }

        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->asOfDate = now()->toDateString();

        $this->loadCurrentReport();
    }

    public function setSection(string $section): void
    {
        $this->activeSection = $section;
        $this->loadCurrentReport();
    }

    public function updatedPeriodPreset(): void
    {
        $range = app(ReportingCenterService::class)->resolveDateRange($this->getFilterPayload());
        $this->dateFrom = $range['start_date'];
        $this->dateTo = $range['end_date'];
        $this->asOfDate = $range['as_of_str'];
        $this->loadCurrentReport();
    }

    public function applyFilters(): void
    {
        $this->loadCurrentReport();
        Notification::make()
            ->title('تم تحديث بيانات التقرير')
            ->success()
            ->send();
    }

    public function resetFilters(): void
    {
        $this->periodPreset = 'this_month';
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
        $this->asOfDate = now()->toDateString();
        $this->selectedCourseId = null;
        $this->selectedGroupId = null;
        $this->selectedVaultId = null;

        $user = auth()->user();
        if ($user && ($user->is_super_admin || $user->hasRole('super_admin'))) {
            $this->selectedBranchId = null;
        }

        $this->loadCurrentReport();
    }

    public function getFilterPayload(): array
    {
        return [
            'period_preset' => $this->periodPreset,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'as_of_date' => $this->asOfDate,
            'branch_id' => $this->selectedBranchId,
            'course_id' => $this->selectedCourseId,
            'group_id' => $this->selectedGroupId,
            'vault_id' => $this->selectedVaultId,
        ];
    }

    public function loadCurrentReport(): void
    {
        $service = app(ReportingCenterService::class);
        $filters = $this->getFilterPayload();
        $user = auth()->user();

        switch ($this->activeSection) {
            case 'overview':
                $this->reportData = $service->getExecutiveOverview($filters, $user);
                break;
            case 'growth':
                $this->reportData = $service->getStudentGrowthReport($filters, $user);
                break;
            case 'enrollments':
                $this->reportData = $service->getEnrollmentsAndRenewalsReport($filters, $user);
                break;
            case 'collections':
                $this->reportData = $service->getActualCollectionsReport($filters, $user);
                break;
            case 'installments':
                $this->reportData = $service->getInstallmentsReport($filters, $user);
                break;
            case 'vaults':
                $this->reportData = $service->getVaultReport($filters, $user);
                break;
            case 'pnl':
                $this->reportData = $service->getRevenueAndExpenseReport($filters, $user);
                break;
            case 'salaries':
                $this->reportData = $service->getInstructorSalaryReport($filters, $user);
                break;
            case 'cancellations':
                $this->reportData = $service->getCancellationAndRefundReport($filters, $user);
                break;
            case 'academic':
                $this->reportData = $service->getAcademicPerformanceReport($filters, $user);
                break;
            case 'supervision':
                $this->reportData = $service->getEducationalSupervisionReport($filters, $user);
                break;
            case 'employees':
                $this->reportData = $service->getEmployeeOperationalReport($filters, $user);
                break;
            case 'branches':
                $this->reportData = $service->getBranchComparisonReport($filters, $user);
                break;
            case 'alerts':
                $this->reportData = [
                    'alerts' => $service->getManagementAlerts($this->selectedBranchId, $this->asOfDate)->toArray(),
                ];
                break;
            case 'saved':
                $this->reportData = [
                    'saved' => SavedReport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->toArray(),
                    'scheduled' => ScheduledReport::where('user_id', $user->id)->orderBy('created_at', 'desc')->get()->toArray(),
                ];
                break;
            case 'settings':
                $setting = ReportSetting::firstOrCreate(
                    ['tenant_id' => $user->tenant_id],
                    [
                        'default_period' => 'this_month',
                        'alert_threshold_debt' => 1000.00,
                        'alert_threshold_low_score_percentage' => 50.00,
                        'alert_homework_overdue_hours' => 48,
                    ]
                );
                $this->reportData = ['settings' => $setting->toArray()];
                break;
            default:
                $this->reportData = $service->getExecutiveOverview($filters, $user);
                break;
        }
    }

    /**
     * Drill down into underlying records for a specific summary metric.
     */
    public function openDrillDown(string $metricKey): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $service = app(ReportingCenterService::class);
        $filters = $this->getFilterPayload();

        switch ($metricKey) {
            case 'new_students':
                $data = $service->getStudentGrowthReport($filters, $user);
                $this->drillDownTitle = 'سجلات الطلاب الجدد المسجلين خلال الفترة';
                $this->drillDownHeaders = ['#', 'كود الطالب', 'اسم الطالب', 'رقم الهاتف', 'الفرع', 'تاريخ التسجيل', 'الاشتراكات'];
                $this->drillDownRows = array_map(fn($r) => [
                    $r['id'], $r['student_code'], $r['name'], $r['phone'], $r['branch_name'], $r['registration_date'], $r['enrollments_count']
                ], $data['records'] ?? []);
                break;

            case 'enrollments_renewals':
                $data = $service->getEnrollmentsAndRenewalsReport($filters, $user);
                $this->drillDownTitle = 'تفاصيل الاشتراكات والتجديدات';
                $this->drillDownHeaders = ['كود الاشتراك', 'اسم الطالب', 'الكورسات', 'المجموعة', 'نوع العملية', 'التعاقد', 'المدفوع', 'المتبقي', 'الحالة'];
                $this->drillDownRows = array_map(fn($r) => [
                    $r['enrollment_code'], $r['student_name'], $r['courses'], $r['groups'], $r['operation_type'], number_format($r['net_amount'], 2), number_format($r['paid_amount'], 2), number_format($r['remaining_amount'], 2), $r['status']
                ], $data['records'] ?? []);
                break;

            case 'actual_collections':
                $data = $service->getActualCollectionsReport($filters, $user);
                $this->drillDownTitle = 'سجلات سندات التحصيل النقدي الفعلي';
                $this->drillDownHeaders = ['رقم السند', 'تاريخ التحصيل', 'الطالب', 'كود الاشتراك', 'المبلغ', 'طريقة الدفع', 'الخزينة', 'المستلم'];
                $this->drillDownRows = array_map(fn($r) => [
                    $r['payment_code'], $r['payment_date'], $r['student_name'], $r['enrollment_code'], number_format($r['amount'], 2), $r['payment_method'], $r['vault_name'], $r['received_by']
                ], $data['records'] ?? []);
                break;

            case 'overdue_debt':
                $data = $service->getInstallmentsReport($filters, $user);
                $this->drillDownTitle = 'تفاصيل الأقساط والمديونيات المتأخرة';
                $this->drillDownHeaders = ['القسط', 'الطالب', 'كود الاشتراك', 'تاريخ الاستحقاق', 'أصل المبلغ', 'المسدد', 'المتبقي', 'أيام التأخير'];
                $this->drillDownRows = array_map(fn($r) => [
                    'قسط #' . $r['installment_number'], $r['student_name'], $r['enrollment_code'], $r['due_date'], number_format($r['original_amount'], 2), number_format($r['paid_amount'], 2), number_format($r['remaining_amount'], 2), $r['days_overdue'] . ' يوم'
                ], array_filter($data['records'] ?? [], fn($x) => ($x['days_overdue'] ?? 0) > 0));
                break;

            case 'unrecorded_sessions':
                $data = $service->getAcademicPerformanceReport($filters, $user);
                $this->drillDownTitle = 'مؤشرات جلسات الحضور والالتزام الأكاديمي';
                $this->drillDownHeaders = ['البند', 'القيمة'];
                $this->drillDownRows = [
                    ['الجلسات غير المسجل بها الحضور (تأخير رصد)', $data['attendance']['unrecorded_sessions_count'] ?? 0],
                    ['إجمالي السجلات المرصودة', $data['attendance']['total_recorded_entries'] ?? 0],
                    ['حضور مؤكد', $data['attendance']['present_count'] ?? 0],
                    ['غياب مؤكد ومسجل', $data['attendance']['absent_count'] ?? 0],
                    ['نسبة الحضور الموثقة', ($data['attendance']['attendance_rate'] ?? 0) . '%'],
                ];
                break;

            default:
                $this->drillDownTitle = 'تفاصيل المؤشر';
                $this->drillDownHeaders = ['البيان', 'القيمة'];
                $this->drillDownRows = [
                    ['المفتاح المطلوب', $metricKey],
                    ['الفترة المحددة', $this->dateFrom . ' إلى ' . $this->dateTo],
                ];
                break;
        }

        $this->showDrillDownModal = true;
    }

    public function closeDrillDownModal(): void
    {
        $this->showDrillDownModal = false;
        $this->drillDownRows = [];
    }

    /**
     * Save Current Report Configuration.
     */
    public function saveReportConfiguration(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        if (empty(trim($this->savedReportName))) {
            $this->savedReportName = 'تقرير ' . $this->activeSection . ' — ' . now()->format('Y-m-d H:i');
        }

        SavedReport::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'name' => $this->savedReportName,
            'category' => $this->activeSection,
            'filters' => $this->getFilterPayload(),
        ]);

        $this->showSaveReportModal = false;
        $this->savedReportName = '';

        Notification::make()
            ->title('تم حفظ إعدادات التقرير بنجاح')
            ->success()
            ->send();
    }

    /**
     * Export Report securely to CSV, Excel, or PDF.
     */
    public function exportReport(string $format): mixed
    {
        $user = auth()->user();
        if (!$user || (!$user->is_super_admin && !$user->hasRole('super_admin') && !$user->can('export_reporting_center') && !$user->can('export_reports'))) {
            Notification::make()
                ->title('غير مصرح لك بتصدير التقارير')
                ->danger()
                ->send();
            return null;
        }

        $exportService = app(ReportingExportService::class);
        $title = 'تقرير AI School — ' . $this->getSectionTitle($this->activeSection);

        // Prepare tabular export data based on active section
        $exportPayload = $this->prepareExportRows();
        $headers = $exportPayload['headers'];
        $rows = $exportPayload['rows'];

        $metadata = [
            'branch_name' => $this->selectedBranchId ? (Tenant::find($this->selectedBranchId)?->name ?? 'الفرع المحدد') : 'كافة الفروع المتاحة',
            'period_label' => $this->dateFrom . ' إلى ' . $this->dateTo,
            'user_name' => $user->name,
        ];

        if ($format === 'csv') {
            return $exportService->exportCsv($title, $headers, $rows);
        }

        if ($format === 'xlsx') {
            return $exportService->exportExcel($title, $headers, $rows);
        }

        if ($format === 'pdf') {
            $pdfContent = $exportService->exportPdf($title, $headers, $rows, $metadata);
            return response()->streamDownload(function () use ($pdfContent) {
                echo $pdfContent;
            }, 'report_' . Carbon::now()->format('Ymd_His') . '.pdf', [
                'Content-Type' => 'application/pdf',
            ]);
        }

        return null;
    }

    protected function prepareExportRows(): array
    {
        switch ($this->activeSection) {
            case 'growth':
                $headers = ['الكود', 'الاسم', 'الهاتف', 'الفرع', 'تاريخ التسجيل', 'الاشتراكات'];
                $rows = array_map(fn($r) => [
                    $r['student_code'], $r['name'], $r['phone'], $r['branch_name'], $r['registration_date'], $r['enrollments_count']
                ], $this->reportData['records'] ?? []);
                return compact('headers', 'rows');

            case 'enrollments':
                $headers = ['كود الاشتراك', 'اسم الطالب', 'الكورسات', 'النوع', 'التعاقد', 'المدفوع', 'المتبقي', 'الحالة'];
                $rows = array_map(fn($r) => [
                    $r['enrollment_code'], $r['student_name'], $r['courses'], $r['operation_type'], $r['net_amount'], $r['paid_amount'], $r['remaining_amount'], $r['status']
                ], $this->reportData['records'] ?? []);
                return compact('headers', 'rows');

            case 'collections':
                $headers = ['رقم السند', 'تاريخ التحصيل', 'اسم الطالب', 'كود الاشتراك', 'المبلغ', 'طريقة الدفع', 'الخزينة'];
                $rows = array_map(fn($r) => [
                    $r['payment_code'], $r['payment_date'], $r['student_name'], $r['enrollment_code'], $r['amount'], $r['payment_method'], $r['vault_name']
                ], $this->reportData['records'] ?? []);
                return compact('headers', 'rows');

            case 'installments':
                $headers = ['القسط', 'الطالب', 'كود الاشتراك', 'تاريخ الاستحقاق', 'أصل المبلغ', 'المسدد', 'المتبقي', 'أيام التأخير'];
                $rows = array_map(fn($r) => [
                    'قسط #' . $r['installment_number'], $r['student_name'], $r['enrollment_code'], $r['due_date'], $r['original_amount'], $r['paid_amount'], $r['remaining_amount'], $r['days_overdue']
                ], $this->reportData['records'] ?? []);
                return compact('headers', 'rows');

            case 'vaults':
                $headers = ['الخزينة', 'الرصيد الافتتاحي', 'المقبوضات النقدية', 'التحويلات الواردة', 'المصروفات', 'المرتبات', 'الاستردادات', 'الرصيد الختامي'];
                $rows = array_map(fn($r) => [
                    $r['vault_name'], $r['opening_balance'], $r['cash_receipts'], $r['transfers_in'], $r['expenses'], $r['salaries'], $r['refunds'], $r['closing_balance']
                ], $this->reportData['vaults'] ?? []);
                return compact('headers', 'rows');

            case 'overview':
            default:
                $headers = ['المؤشر الإداري / المالي', 'القيمة المحسوبة'];
                $rows = [
                    ['الطلاب الجدد في الفترة', $this->reportData['new_students_count'] ?? 0],
                    ['إجمالي حركات الاشتراكات والتجديدات', $this->reportData['total_enrollments_count'] ?? 0],
                    ['القيمة التعاقدية للاشتراكات (ج.م)', number_format($this->reportData['contracted_enrollment_value'] ?? 0, 2)],
                    ['التحصيلات النقدية الفعلية (ج.م)', number_format($this->reportData['actual_collections'] ?? 0, 2)],
                    ['إجمالي مديونيات الطلاب المستحقة (ج.م)', number_format($this->reportData['total_outstanding_debt'] ?? 0, 2)],
                    ['المديونيات المتأخرة (Overdue) (ج.م)', number_format($this->reportData['overdue_debt'] ?? 0, 2)],
                    ['المستحق اليوم (Currently Due) (ج.م)', number_format($this->reportData['currently_due_debt'] ?? 0, 2)],
                    ['الأقساط المستقبلية (Future Installments) (ج.م)', number_format($this->reportData['future_installments_debt'] ?? 0, 2)],
                    ['التدفقات النقدية الداخلة (ج.م)', number_format($this->reportData['actual_cash_inflows'] ?? 0, 2)],
                    ['المدفوعات النقدية الخارجة (ج.م)', number_format($this->reportData['actual_cash_disbursements'] ?? 0, 2)],
                    ['صافي الحركة النقدية (ج.م)', number_format($this->reportData['net_cash_movement'] ?? 0, 2)],
                    ['الجلسات المكتملة', $this->reportData['completed_sessions_count'] ?? 0],
                    ['الجلسات غير المسجل بها الحضور', $this->reportData['unrecorded_attendance_sessions_count'] ?? 0],
                    ['نسبة الانضباط في تسجيل الحضور', ($this->reportData['attendance_compliance_rate'] ?? 0) . '%'],
                    ['الملاحظات الإشرافية المفتوحة', $this->reportData['open_supervisory_observations'] ?? 0],
                    ['التنبيهات الإدارية القائمة', $this->reportData['management_alerts_count'] ?? 0],
                ];
                return compact('headers', 'rows');
        }
    }

    public function getSectionTitle(string $section): string
    {
        return match ($section) {
            'overview' => 'نظرة تنفيذية شاملة',
            'growth' => 'نمو الطلاب والتسجيل',
            'enrollments' => 'الاشتراكات والتجديدات الموحدة',
            'collections' => 'التحصيلات النقدية الفعلية',
            'installments' => 'الأقساط وإعمار الديون',
            'vaults' => 'حركة الخزائن والسيولة',
            'pnl' => 'الإيرادات والمصروفات وقائمة الدخل',
            'salaries' => 'مستحقات ومرتبات المهندسين',
            'cancellations' => 'الإلغاءات والتسويات والاستردادات',
            'academic' => 'التحليل الأكاديمي والامتحانات',
            'supervision' => 'تقارير الإشراف التربوي',
            'employees' => 'العمليات التشغيلية للموظفين',
            'branches' => 'مقارنة الفروع والمؤشرات',
            'alerts' => 'التنبيهات الإدارية والاستثناءات',
            'saved' => 'التقارير المحفوظة والمجدولة',
            'settings' => 'إعدادات مركز التقارير',
            default => 'التقرير الموحد',
        };
    }

    public function getAvailableBranchesProperty()
    {
        $user = auth()->user();
        if ($user && ($user->is_super_admin || $user->hasRole('super_admin') || is_null($user->tenant_id))) {
            return Tenant::all();
        }

        return Tenant::where('id', $user?->tenant_id)->get();
    }

    public function getAvailableCoursesProperty()
    {
        return Course::when($this->selectedBranchId, fn($q) => $q->where('tenant_id', $this->selectedBranchId))->get();
    }

    public function getAvailableGroupsProperty()
    {
        return Group::when($this->selectedBranchId, fn($q) => $q->where('tenant_id', $this->selectedBranchId))
            ->when($this->selectedCourseId, fn($q) => $q->where('course_id', $this->selectedCourseId))
            ->get();
    }

    public function getAvailableVaultsProperty()
    {
        return Vault::where('is_active', true)
            ->when($this->selectedBranchId, fn($q) => $q->where('tenant_id', $this->selectedBranchId))
            ->get();
    }
}

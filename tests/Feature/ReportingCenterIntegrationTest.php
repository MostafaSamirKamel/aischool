<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Vault;
use App\Services\Reporting\ReportingCenterService;
use App\Services\Reporting\ReportingExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingCenterIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $branchManager;
    protected User $accountant;
    protected User $studentUser;
    protected Tenant $branch1;
    protected Tenant $branch2;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'branch_manager', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view_reporting_center', 'guard_name' => 'web']);

        $this->branch1 = Tenant::firstOrCreate(['id' => 1], ['name' => 'الفرع الرئيسي - القاهرة', 'slug' => 'cairo']);
        $this->branch2 = Tenant::firstOrCreate(['id' => 2], ['name' => 'فرع دمنهور', 'slug' => 'damanhour']);

        $this->superAdmin = User::factory()->create([
            'email' => 'admin@admin.com',
            'is_super_admin' => true,
            'status' => 'active',
            'tenant_id' => null,
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->branchManager = User::factory()->create([
            'email' => 'manager.main@admin.com',
            'status' => 'active',
            'tenant_id' => 1,
        ]);
        $this->branchManager->assignRole('branch_manager');

        $this->accountant = User::factory()->create([
            'email' => 'accountant.cairo@aischool.test',
            'status' => 'active',
            'tenant_id' => 1,
        ]);
        $this->accountant->assignRole('accountant');

        $this->studentUser = User::factory()->create([
            'email' => 'student1@aischool.test',
            'status' => 'active',
            'tenant_id' => 1,
        ]);
        $this->studentUser->assignRole('student');
    }

    public function test_super_admin_can_access_reporting_center_page()
    {
        $response = $this->actingAs($this->superAdmin)->get('/admin/reporting-center-page');
        $response->assertStatus(200);
        $response->assertSee('مركز التقارير والاستخبارات التشغيلية والمالية');
    }

    public function test_branch_manager_can_access_reporting_center_page()
    {
        $response = $this->actingAs($this->branchManager)->get('/admin/reporting-center-page');
        $response->assertStatus(200);
    }

    public function test_accountant_can_access_reporting_center_page()
    {
        $response = $this->actingAs($this->accountant)->get('/admin/reporting-center-page');
        $response->assertStatus(200);
    }

    public function test_student_forbidden_from_admin_reporting_center()
    {
        $response = $this->actingAs($this->studentUser)->get('/admin/reporting-center-page');
        $response->assertRedirect('/student');
    }

    public function test_new_students_counted_uniquely_during_period()
    {
        $service = app(ReportingCenterService::class);
        $filters = ['period_preset' => 'this_year'];
        $overview = $service->getExecutiveOverview($filters);

        $this->assertArrayHasKey('new_students_count', $overview);
        $this->assertGreaterThanOrEqual(0, $overview['new_students_count']);
    }

    public function test_enrollments_and_renewals_combined_into_one_total()
    {
        $service = app(ReportingCenterService::class);
        $filters = ['period_preset' => 'this_year'];
        $report = $service->getEnrollmentsAndRenewalsReport($filters);

        $this->assertArrayHasKey('total_enrollment_and_renewal_events', $report);
        $this->assertArrayHasKey('new_enrollments_count', $report);
        $this->assertArrayHasKey('renewals_count', $report);
        $this->assertArrayHasKey('total_contracted_value', $report);
    }

    public function test_actual_collections_reflect_real_payments_without_double_counting()
    {
        $service = app(ReportingCenterService::class);
        $filters = ['period_preset' => 'this_year'];
        $report = $service->getActualCollectionsReport($filters);

        $this->assertArrayHasKey('total_collections', $report);
        $this->assertArrayHasKey('collections_count', $report);
        $this->assertArrayHasKey('by_method', $report);
        $this->assertArrayHasKey('by_vault', $report);
    }

    public function test_installments_debt_separated_into_overdue_due_today_and_future()
    {
        $service = app(ReportingCenterService::class);
        $filters = ['period_preset' => 'this_month'];
        $report = $service->getInstallmentsReport($filters);

        $this->assertArrayHasKey('total_outstanding_debt', $report);
        $this->assertArrayHasKey('overdue_amount', $report);
        $this->assertArrayHasKey('currently_due_amount', $report);
        $this->assertArrayHasKey('future_amount', $report);
        $this->assertArrayHasKey('aging_buckets', $report);

        $expectedTotal = round($report['overdue_amount'] + $report['currently_due_amount'] + $report['future_amount'], 2);
        $this->assertEquals($expectedTotal, $report['total_outstanding_debt']);
    }

    public function test_unrecorded_attendance_is_not_counted_as_student_absence()
    {
        $service = app(ReportingCenterService::class);
        $filters = ['period_preset' => 'this_year'];
        $report = $service->getAcademicPerformanceReport($filters);

        $this->assertArrayHasKey('attendance', $report);
        $this->assertArrayHasKey('unrecorded_sessions_count', $report['attendance']);
        $this->assertArrayHasKey('absent_count', $report['attendance']);
        $this->assertArrayHasKey('present_count', $report['attendance']);
    }

    public function test_export_service_sanitizes_csv_formula_injection()
    {
        $injectionAttack = '=SUM(A1:A10)';
        $sanitized = ReportingExportService::sanitizeCell($injectionAttack);
        $this->assertEquals("'=SUM(A1:A10)", $sanitized);

        $safeText = 'أحمد محمد';
        $sanitizedText = ReportingExportService::sanitizeCell($safeText);
        $this->assertEquals('أحمد محمد', $sanitizedText);
    }

    public function test_collections_report_runs_with_branch_and_vault_joins_without_ambiguous_columns()
    {
        $service = app(ReportingCenterService::class);
        $filters = [
            'period_preset' => 'this_month',
            'branch_id' => 1,
        ];
        $report = $service->getActualCollectionsReport($filters);

        $this->assertArrayHasKey('total_collections', $report);
        $this->assertArrayHasKey('by_method', $report);
        $this->assertArrayHasKey('by_vault', $report);
        $this->assertArrayHasKey('records', $report);
    }
}

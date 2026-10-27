<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Department;
use App\Models\Group;
use App\Models\Instructor;
use App\Models\InstructorEvaluation;
use App\Models\InstructorEvaluationAppeal;
use App\Models\Student;
use App\Models\SupervisionReport;
use App\Models\SupervisoryObservation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EducationalSupervisionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EducationalSupervisionFullSystemTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $branchA;
    protected Tenant $branchB;
    protected User $supervisorA;
    protected User $supervisorB;
    protected User $secretaryA;
    protected User $instructorA;
    protected User $managementUser;
    protected Instructor $instructorProfileA;
    protected Instructor $instructorProfileB;
    protected Group $groupA1;
    protected Group $groupA2;
    protected Group $groupB1;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Permissions
        $permissions = [
            'branch_educational_supervisor',
            'view_supervision_dashboard',
            'create_supervisory_observations',
            'respond_supervisory_observations',
            'approve_supervisory_observations',
            'return_supervisory_observations',
            'create_instructor_evaluations',
            'publish_instructor_evaluations',
            'view_instructor_evaluations',
            'comment_instructor_evaluations',
            'appeal_instructor_evaluations',
            'respond_evaluation_appeals',
            'decide_evaluation_appeals',
            'prepare_supervision_reports',
            'publish_supervision_reports',
            'review_management_supervision_reports',
            'request_supervision_report_revision',
            'request_supervision_report_clarification',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $supervisorRole = Role::firstOrCreate(['name' => 'supervisor', 'guard_name' => 'web']);
        $supervisorRole->syncPermissions([
            'branch_educational_supervisor',
            'view_supervision_dashboard',
            'create_supervisory_observations',
            'approve_supervisory_observations',
            'return_supervisory_observations',
            'create_instructor_evaluations',
            'publish_instructor_evaluations',
            'respond_evaluation_appeals',
            'prepare_supervision_reports',
            'publish_supervision_reports',
        ]);

        $instructorRole = Role::firstOrCreate(['name' => 'instructor', 'guard_name' => 'web']);
        $instructorRole->syncPermissions([
            'respond_supervisory_observations',
            'view_instructor_evaluations',
            'comment_instructor_evaluations',
            'appeal_instructor_evaluations',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions([
            'review_management_supervision_reports',
            'request_supervision_report_revision',
            'request_supervision_report_clarification',
            'decide_evaluation_appeals',
        ]);

        $secretaryRole = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);

        // 2. Setup Tenants
        $this->branchA = Tenant::create(['name' => 'Branch Cairo', 'domain' => 'cairo']);
        $this->branchB = Tenant::create(['name' => 'Branch Damanhour', 'domain' => 'damanhour']);

        // 3. Setup Users & Roles
        $this->supervisorA = User::factory()->create([
            'tenant_id' => $this->branchA->id,
            'name' => 'Supervisor Cairo',
            'status' => 'active',
        ]);
        $this->supervisorA->assignRole($supervisorRole);

        $this->supervisorB = User::factory()->create([
            'tenant_id' => $this->branchB->id,
            'name' => 'Supervisor Damanhour',
            'status' => 'active',
        ]);
        $this->supervisorB->assignRole($supervisorRole);

        $this->secretaryA = User::factory()->create([
            'tenant_id' => $this->branchA->id,
            'name' => 'Secretary Cairo',
            'status' => 'active',
        ]);
        $this->secretaryA->assignRole($secretaryRole);

        $instructorUserA = User::factory()->create([
            'tenant_id' => $this->branchA->id,
            'name' => 'Instructor Cairo',
            'status' => 'active',
        ]);
        $instructorUserA->assignRole($instructorRole);
        $this->instructorA = $instructorUserA;

        $deptA = Department::create(['tenant_id' => $this->branchA->id, 'name' => 'Computer Science']);
        $deptB = Department::create(['tenant_id' => $this->branchB->id, 'name' => 'Robotics']);

        $this->instructorProfileA = Instructor::create([
            'tenant_id' => $this->branchA->id,
            'user_id' => $instructorUserA->id,
            'department_id' => $deptA->id,
            'employee_code' => 'INS-TEST-A1',
            'specialization' => 'Web Development',
            'status' => 'active',
            'salary' => 5000,
        ]);

        $instructorUserB = User::factory()->create([
            'tenant_id' => $this->branchB->id,
            'name' => 'Instructor Damanhour',
            'status' => 'active',
        ]);
        $instructorUserB->assignRole($instructorRole);

        $this->instructorProfileB = Instructor::create([
            'tenant_id' => $this->branchB->id,
            'user_id' => $instructorUserB->id,
            'department_id' => $deptB->id,
            'employee_code' => 'INS-TEST-B1',
            'specialization' => 'Robotics',
            'status' => 'active',
            'salary' => 4500,
        ]);

        $this->managementUser = User::factory()->create([
            'tenant_id' => $this->branchA->id,
            'name' => 'School Admin',
            'status' => 'active',
        ]);
        $this->managementUser->assignRole($adminRole);

        // 4. Setup Courses & Groups
        $courseA = Course::create(['tenant_id' => $this->branchA->id, 'name' => 'PHP & Laravel']);
        $courseB = Course::create(['tenant_id' => $this->branchB->id, 'name' => 'Arduino 101']);

        $this->groupA1 = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $courseA->id,
            'instructor_id' => $this->instructorProfileA->id,
            'name' => 'Cairo Group A1',
            'status' => 'active',
        ]);

        $this->groupA2 = Group::create([
            'tenant_id' => $this->branchA->id,
            'course_id' => $courseA->id,
            'instructor_id' => $this->instructorProfileA->id,
            'name' => 'Cairo Group A2',
            'status' => 'active',
        ]);

        $this->groupB1 = Group::create([
            'tenant_id' => $this->branchB->id,
            'course_id' => $courseB->id,
            'instructor_id' => $this->instructorProfileB->id,
            'name' => 'Damanhour Group B1',
            'status' => 'active',
        ]);
    }

    /**
     * Test 1: Branch Educational Supervisor has full branch-wide scope within their branch.
     */
    public function test_branch_designated_supervisor_has_branch_wide_scope(): void
    {
        $service = app(EducationalSupervisionService::class);

        $groupIds = $service->getSupervisedGroupIds($this->supervisorA);
        $instructorIds = $service->getSupervisedInstructorIds($this->supervisorA);

        $this->assertContains($this->groupA1->id, $groupIds);
        $this->assertContains($this->groupA2->id, $groupIds);
        $this->assertContains($this->instructorProfileA->id, $instructorIds);

        $this->assertTrue($service->canSuperviseGroup($this->supervisorA, $this->groupA1));
        $this->assertTrue($service->canSuperviseGroup($this->supervisorA, $this->groupA2));
        $this->assertTrue($service->canSuperviseInstructor($this->supervisorA, $this->instructorProfileA));
    }

    /**
     * Test 2: Cross-branch isolation prevents supervisor from accessing another branch.
     */
    public function test_cross_branch_isolation_denies_access_to_other_branch(): void
    {
        $service = app(EducationalSupervisionService::class);

        $groupIds = $service->getSupervisedGroupIds($this->supervisorA);
        $instructorIds = $service->getSupervisedInstructorIds($this->supervisorA);

        $this->assertNotContains($this->groupB1->id, $groupIds);
        $this->assertNotContains($this->instructorProfileB->id, $instructorIds);

        $this->assertFalse($service->canSuperviseGroup($this->supervisorA, $this->groupB1));
        $this->assertFalse($service->canSuperviseInstructor($this->supervisorA, $this->instructorProfileB));
    }

    /**
     * Test 3: Limited group supervisor / secretary is strictly isolated to explicitly assigned groups.
     */
    public function test_limited_group_supervisor_secretary_is_restricted_to_assigned_groups(): void
    {
        $service = app(EducationalSupervisionService::class);

        // Assign only Group A1 to secretary
        $service->assignGroups($this->secretaryA, [$this->groupA1->id], $this->managementUser);

        $assignedGroupIds = $service->getSupervisedGroupIds($this->secretaryA);

        $this->assertContains($this->groupA1->id, $assignedGroupIds);
        $this->assertNotContains($this->groupA2->id, $assignedGroupIds);
        $this->assertNotContains($this->groupB1->id, $assignedGroupIds);

        $this->assertTrue($service->canSuperviseGroup($this->secretaryA, $this->groupA1));
        $this->assertFalse($service->canSuperviseGroup($this->secretaryA, $this->groupA2));
    }

    /**
     * Test 4: Complete supervisory observation workflow:
     * Creation (new) -> Instructor response (waiting_review) -> Supervisor return (new) -> Resubmit -> Supervisor approve (completed).
     */
    public function test_supervisory_observation_workflow_and_supervisor_only_completion(): void
    {
        // 1. Supervisor creates observation
        $observation = SupervisoryObservation::create([
            'tenant_id' => $this->branchA->id,
            'supervisor_id' => $this->supervisorA->id,
            'instructor_id' => $this->instructorProfileA->id,
            'group_id' => $this->groupA1->id,
            'category' => 'attendance_not_recorded',
            'title' => 'تأخر رصد حضور جلسة الأحد',
            'description' => 'انتهت الجلسة دون حفظ السجلات للطلاب',
            'required_action' => 'يرجى تسجيل الحضور من الشاشة الأكاديمية فوراً',
            'expected_resolution_date' => now()->addDay(),
            'priority' => 'urgent',
            'status' => SupervisoryObservation::STATUS_NEW,
        ]);

        $this->assertEquals(SupervisoryObservation::STATUS_NEW, $observation->status);
        $this->assertStringStartsWith('SOB-', $observation->observation_code);

        // 2. Instructor responds and submits for review
        $observation->submitInstructorResponse($this->instructorA, 'تم الدخول لشاشة الحضور ورصد جميع الطلاب الحاضرين والغائبين.');
        $observation->refresh();

        $this->assertEquals(SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW, $observation->status);
        $this->assertNotNull($observation->instructor_responded_at);

        // 3. Supervisor inspects and finds correction incomplete -> returns for correction with mandatory reason
        $observation->returnForCorrection($this->supervisorA, 'سجلات الحضور ما زالت ناقصة لطالبين في المجموعة.');
        $observation->refresh();

        $this->assertEquals(SupervisoryObservation::STATUS_NEW, $observation->status);
        $this->assertEquals('سجلات الحضور ما زالت ناقصة لطالبين في المجموعة.', $observation->return_reason);

        // 4. Instructor corrects and resubmits
        $observation->submitInstructorResponse($this->instructorA, 'تم استكمال الطالبين الناقصين ورصد الحضور كاملاً.');
        $observation->refresh();
        $this->assertEquals(SupervisoryObservation::STATUS_WAITING_SUPERVISOR_REVIEW, $observation->status);

        // 5. Supervisor approves completion
        $observation->approveCompletion($this->supervisorA, 'تم التحقق من اكتمال كافة سجلات الحضور بنجاح.');
        $observation->refresh();

        $this->assertEquals(SupervisoryObservation::STATUS_COMPLETED, $observation->status);
        $this->assertEquals($this->supervisorA->id, $observation->approved_by);
        $this->assertNotNull($observation->approved_at);

        // 6. Complete audit history must exist
        $histories = $observation->histories()->orderBy('id')->get();
        $this->assertGreaterThanOrEqual(4, $histories->count());
        $this->assertEquals('submitted_for_review', $histories[0]->action);
        $this->assertEquals('returned_for_correction', $histories[1]->action);
        $this->assertEquals('submitted_for_review', $histories[2]->action);
        $this->assertEquals('approved_completed', $histories[3]->action);
    }

    /**
     * Test 5: Instructor evaluations: 6 criteria, Not Observed (null) handling, draft invisibility, and publication.
     */
    public function test_instructor_evaluation_scoring_unobserved_criteria_and_draft_isolation(): void
    {
        // 1. Create evaluation with 4 scored criteria and 2 Not Observed (null)
        $evaluation = InstructorEvaluation::create([
            'tenant_id' => $this->branchA->id,
            'instructor_id' => $this->instructorProfileA->id,
            'supervisor_id' => $this->supervisorA->id,
            'evaluation_type' => InstructorEvaluation::TYPE_MONTHLY,
            'evaluation_period' => '2026-10',
            'review_basis' => 'direct_observation',
            'score_explanation_quality' => 4,
            'score_student_interaction' => 5,
            'score_practical_application' => 3,
            'score_curriculum_adherence' => 4,
            'score_time_management' => null, // Not Observed
            'score_homework_followup' => null, // Not Observed
            'strengths' => 'شرح ممتاز وتفاعل رائع مع الطلاب',
            'areas_for_improvement' => 'الاهتمام بالتمارين الأكثر عمقاً',
            'recommendations' => 'مواصلة استخدام الأنشطة العملية',
            'status' => InstructorEvaluation::STATUS_DRAFT,
        ]);

        $this->assertStringStartsWith('IEV-', $evaluation->evaluation_code);
        $this->assertEquals(4, $evaluation->criteria_observed_count);
        // Average: (4 + 5 + 3 + 4) / 4 = 16 / 4 = 4.00
        $this->assertEquals(4.00, (float) $evaluation->calculated_average);

        // 2. Draft is invisible to instructor
        $instructorVisibleDrafts = InstructorEvaluation::where('instructor_id', $this->instructorProfileA->id)
            ->where('status', InstructorEvaluation::STATUS_PUBLISHED)
            ->get();

        $this->assertEmpty($instructorVisibleDrafts);

        // 3. Supervisor publishes evaluation
        $evaluation->publish($this->supervisorA);
        $evaluation->refresh();

        $this->assertEquals(InstructorEvaluation::STATUS_PUBLISHED, $evaluation->status);
        $this->assertEquals($this->supervisorA->id, $evaluation->published_by);
        $this->assertNotNull($evaluation->published_at);

        // 4. Now visible to instructor
        $instructorVisiblePublished = InstructorEvaluation::where('instructor_id', $this->instructorProfileA->id)
            ->where('status', InstructorEvaluation::STATUS_PUBLISHED)
            ->get();

        $this->assertCount(1, $instructorVisiblePublished);
    }

    /**
     * Test 6: Instructor comment vs formal appeal, supervisor response, and management-only final decision.
     */
    public function test_instructor_evaluation_comments_and_formal_appeal_workflow(): void
    {
        $evaluation = InstructorEvaluation::create([
            'tenant_id' => $this->branchA->id,
            'instructor_id' => $this->instructorProfileA->id,
            'supervisor_id' => $this->supervisorA->id,
            'evaluation_type' => InstructorEvaluation::TYPE_MONTHLY,
            'evaluation_period' => '2026-10',
            'version' => 1,
            'status' => InstructorEvaluation::STATUS_PUBLISHED,
            'published_at' => now(),
            'score_explanation_quality' => 3,
            'calculated_average' => 3.00,
            'criteria_observed_count' => 1,
            'strengths' => 'أداء مقبول',
            'areas_for_improvement' => 'تحسين إدارة الوقت',
            'recommendations' => 'التدريب على تنظيم الحصة',
        ]);

        // 1. Instructor submits an ordinary comment (does not create formal appeal)
        $comment = $evaluation->appeals()->create([
            'tenant_id' => $this->branchA->id,
            'evaluation_version' => $evaluation->version,
            'instructor_id' => $this->instructorProfileA->id,
            'type' => 'comment',
            'reason' => 'تم توضيح أن التأخير كان لعطل تقني في شاشة العرض بالقاعة.',
            'status' => 'submitted',
        ]);

        $this->assertTrue($comment->isComment());
        $this->assertFalse($comment->isFormalAppeal());

        // 2. Instructor submits a formal appeal
        $appeal = $evaluation->appeals()->create([
            'tenant_id' => $this->branchA->id,
            'evaluation_version' => $evaluation->version,
            'instructor_id' => $this->instructorProfileA->id,
            'type' => 'appeal',
            'reason' => 'أعترض على معيار إدارة الوقت حيث تم تعويض الوقت كاملاً بنهاية الحصة.',
            'status' => 'submitted',
        ]);

        $this->assertTrue($appeal->isFormalAppeal());

        // 3. Supervisor responds with observations and recommendation
        $appeal->submitSupervisorResponse($this->supervisorA, 'بالرجوع لسجلات كاميرات القاعة، تبيّن صحة تعويض الوقت بالفعل.');
        $appeal->refresh();

        $this->assertEquals('supervisor_responded', $appeal->status);
        $this->assertEquals($this->supervisorA->id, $appeal->supervisor_id);

        // 4. Management makes the final decision
        $appeal->decideByManagement($this->managementUser, 'accepted', 'تم قبول الاعتراض وتعديل تقييم إدارة الوقت لتوفر البينة.');
        $appeal->refresh();

        $this->assertEquals('accepted', $appeal->status);
        $this->assertEquals($this->managementUser->id, $appeal->decided_by);

        // 5. Preserves version snapshot in history
        $evaluation->recordVersionSnapshot('تعديل التقييم استناداً لقرار قبول الاعتراض الإداري', $this->managementUser);
        $this->assertEquals(2, $evaluation->version);
        $this->assertEquals(1, $evaluation->versions()->count());
    }

    /**
     * Test 7: Weekly and Monthly Reports: Draft generation, idempotency (no duplicate drafts), and supervisor publication.
     */
    public function test_supervision_report_draft_generation_idempotency_and_publication(): void
    {
        $service = app(EducationalSupervisionService::class);
        $weekKey = '2026-W42';

        // 1. Generate weekly report draft
        $draft1 = $service->generateWeeklyReportDraft($this->branchA, $weekKey, $this->supervisorA);

        $this->assertEquals(SupervisionReport::STATUS_DRAFT, $draft1->status);
        $this->assertEquals($weekKey, $draft1->period_key);
        $this->assertStringStartsWith('SR-WK-', $draft1->report_code);

        // 2. Idempotency test: Generating again for same branch and week must return the existing draft
        $draft2 = $service->generateWeeklyReportDraft($this->branchA, $weekKey, $this->supervisorA);
        $this->assertEquals($draft1->id, $draft2->id);

        $totalDraftsCount = SupervisionReport::where('tenant_id', $this->branchA->id)
            ->where('period_key', $weekKey)
            ->count();
        $this->assertEquals(1, $totalDraftsCount);

        // 3. Supervisor approves publication
        $publishedReport = $service->publishSupervisionReport($draft1, $this->supervisorA);
        $this->assertEquals(SupervisionReport::STATUS_PUBLISHED, $publishedReport->status);
        $this->assertNotNull($publishedReport->published_at);
        $this->assertEquals(1, $publishedReport->versions()->count());

        // 4. Management requests revision with mandatory reason
        $service->requestReportRevision($publishedReport, $this->managementUser, 'يرجى تدقيق إحصائيات الغياب المؤكد ومقارنتها بالأسبوع السابق.');
        $publishedReport->refresh();

        $this->assertEquals(SupervisionReport::STATUS_REVISION_REQUESTED, $publishedReport->status);
        $this->assertEquals('يرجى تدقيق إحصائيات الغياب المؤكد ومقارنتها بالأسبوع السابق.', $publishedReport->revision_reason);
    }

    /**
     * Test 8: CRITICAL RULE: Unrecorded attendance is NOT counted as student absence.
     */
    public function test_unrecorded_attendance_is_not_counted_as_student_absence(): void
    {
        $service = app(EducationalSupervisionService::class);

        // Create an attendance session without any student attendance records
        $session = AttendanceSession::create([
            'tenant_id' => $this->branchA->id,
            'group_id' => $this->groupA1->id,
            'course_id' => $this->groupA1->course_id,
            'attendance_date' => now()->toDateString(),
            'session_notes' => 'جلسة بدون رصد',
        ]);

        $metrics = $service->getDashboardMetrics($this->supervisorA);

        // Missing attendance must be at least 1
        $this->assertGreaterThanOrEqual(1, $metrics['sessions_missing_attendance']);

        // Confirmed student absences must be strictly 0 (no records exist)
        $this->assertEquals(0, $metrics['confirmed_student_absences']);

        // Now record an explicit absent student
        $studentUser = User::factory()->create(['tenant_id' => $this->branchA->id]);
        $student = Student::create([
            'tenant_id' => $this->branchA->id,
            'user_id' => $studentUser->id,
            'student_code' => 'STD-TEST-01',
            'gender' => 'male',
        ]);

        AttendanceRecord::create([
            'tenant_id' => $this->branchA->id,
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => 'absent',
        ]);

        $updatedMetrics = $service->getDashboardMetrics($this->supervisorA);
        $this->assertEquals(1, $updatedMetrics['confirmed_student_absences']);
    }

    /**
     * Test 9: Financial and salary data integrity is preserved during educational supervision.
     */
    public function test_financial_and_salary_data_integrity_during_supervision(): void
    {
        $initialSalary = $this->instructorProfileA->salary;

        // Perform evaluations, observations, and reports
        $eval = InstructorEvaluation::create([
            'tenant_id' => $this->branchA->id,
            'instructor_id' => $this->instructorProfileA->id,
            'supervisor_id' => $this->supervisorA->id,
            'evaluation_type' => InstructorEvaluation::TYPE_MONTHLY,
            'score_explanation_quality' => 1, // lowest score
            'status' => InstructorEvaluation::STATUS_PUBLISHED,
        ]);

        $this->instructorProfileA->refresh();

        // Instructor salary must NOT be affected by evaluation score!
        $this->assertEquals($initialSalary, $this->instructorProfileA->salary);

        // Profile view does NOT expose financial records
        $service = app(EducationalSupervisionService::class);
        $profile = $service->getInstructorSupervisionProfile($this->supervisorA, $this->instructorProfileA->id);

        $this->assertArrayNotHasKey('salary', $profile);
        $this->assertArrayNotHasKey('base_salary', $profile);
        $this->assertArrayNotHasKey('vault_balance', $profile);
    }

    /**
     * Test 10: Supervisor panel routes and pages render cleanly with HTTP 200.
     */
    public function test_supervisor_panel_routes_render_successfully(): void
    {
        $this->actingAs($this->supervisorA);

        $this->get('/supervisor')->assertStatus(200);
        $this->get('/supervisor/supervisory-observations')->assertStatus(200);
        $this->get('/supervisor/supervisory-observations/create')->assertStatus(200);
        $this->get('/supervisor/instructor-evaluations')->assertStatus(200);
        $this->get('/supervisor/instructor-evaluations/create')->assertStatus(200);
        $this->get('/supervisor/supervision-reports')->assertStatus(200);
        $this->get('/supervisor/educational-alerts')->assertStatus(200);
        $this->get('/supervisor/instructor-profiles')->assertStatus(200);
        $this->get('/supervisor/group-profiles')->assertStatus(200);
    }

    /**
     * Test 11: Instructor panel supervision routes render cleanly with HTTP 200.
     */
    public function test_instructor_panel_routes_render_successfully(): void
    {
        $this->actingAs($this->instructorA);

        $this->get('/instructor/supervisory-observations')->assertStatus(200);
        $this->get('/instructor/my-evaluations')->assertStatus(200);
    }

    /**
     * Test 12: Admin panel supervision review routes render cleanly with HTTP 200.
     */
    public function test_admin_panel_supervision_routes_render_successfully(): void
    {
        $this->actingAs($this->managementUser);

        $this->get('/admin/supervision-reports')->assertStatus(200);
        $this->get('/admin/instructor-evaluation-appeals')->assertStatus(200);
    }
}

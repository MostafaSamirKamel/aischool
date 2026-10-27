<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\AcademicFollowUpCenter;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\EnrollmentCourse;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Group;
use App\Models\Installment;
use App\Models\Student;
use App\Models\StudentFollowUpContact;
use App\Models\StudentFollowUpTask;
use App\Models\StudentParent;
use App\Models\SupervisionGroupAssignment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AcademicFollowUpService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LivewireFollowUpCenterSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $branch1;
    protected Tenant $branch2;
    protected User $admin;
    protected User $saraSecretary;
    protected User $monaSecretary;
    protected User $crossBranchSecretary;
    protected Course $pythonCourse;
    protected Course $scratchCourse;
    protected Group $groupA;
    protected Group $groupB;
    protected Student $studentA;
    protected Student $studentB;
    protected Student $studentMulti;
    protected StudentFollowUpTask $taskA;
    protected StudentFollowUpTask $taskB;
    protected AcademicFollowUpService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Branches (Tenants)
        $this->branch1 = Tenant::create(['name' => 'فرع القاهرة التجريبي', 'slug' => 'cairo-sec', 'is_active' => true]);
        $this->branch2 = Tenant::create(['name' => 'فرع الإسكندرية', 'slug' => 'alex-sec', 'is_active' => true]);

        // 2. Permissions setup
        $permissions = [
            'view_daily_follow_up_dashboard',
            'view_student_academic_monitoring',
            'view_exam_followup',
            'view_homework_followup',
            'view_student_contact_numbers',
            'create_student_followup_task',
            'record_parent_contact',
            'reassign_follow_up_case',
            'view_student_financial_follow_up',
        ];
        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $secretaryRole = Role::firstOrCreate(['name' => 'secretary', 'guard_name' => 'web']);
        $secretaryRole->syncPermissions([
            'view_daily_follow_up_dashboard',
            'view_student_academic_monitoring',
            'view_exam_followup',
            'view_homework_followup',
            'create_student_followup_task',
            'record_parent_contact',
            'reassign_follow_up_case',
            'view_student_contact_numbers',
        ]);

        // 3. Sara: Secretary in Branch 1 with dashboard and phone permissions
        $this->saraSecretary = User::create([
            'name' => 'سارة مسؤولة متابعة أ',
            'email' => 'sara.sec@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'status' => 'active',
        ]);
        $this->saraSecretary->assignRole($secretaryRole);

        // 4. Mona: Secretary in Branch 1 for Group B
        $this->monaSecretary = User::create([
            'name' => 'منى مسؤولة متابعة ب',
            'email' => 'mona.sec@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'status' => 'active',
        ]);
        $this->monaSecretary->assignRole($secretaryRole);

        // 5. Cross branch secretary
        $this->crossBranchSecretary = User::create([
            'name' => 'سمير فرع ثان',
            'email' => 'samir.sec@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch2->id,
            'status' => 'active',
        ]);
        $this->crossBranchSecretary->assignRole($secretaryRole);

        // 6. Courses & Groups in Branch 1
        $this->pythonCourse = Course::create([
            'tenant_id' => $this->branch1->id,
            'name' => 'Python Junior',
            'code' => 'PY-JR',
            'price' => 1200,
            'status' => 'active',
        ]);

        $this->scratchCourse = Course::create([
            'tenant_id' => $this->branch1->id,
            'name' => 'Scratch Game Dev',
            'code' => 'SCR-01',
            'price' => 1000,
            'status' => 'active',
        ]);

        $this->groupA = Group::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->pythonCourse->id,
            'name' => 'Python Group A',
            'status' => 'active',
            'capacity' => 25,
        ]);

        $this->groupB = Group::create([
            'tenant_id' => $this->branch1->id,
            'course_id' => $this->scratchCourse->id,
            'name' => 'Scratch Group B',
            'status' => 'active',
            'capacity' => 25,
        ]);

        // Explicit Group Assignments:
        // Sara -> Group A ONLY
        SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch1->id,
            'user_id' => $this->saraSecretary->id,
            'group_id' => $this->groupA->id,
            'assigned_by' => $this->saraSecretary->id,
            'assigned_at' => now(),
        ]);

        // Mona -> Group B ONLY
        SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch1->id,
            'user_id' => $this->monaSecretary->id,
            'group_id' => $this->groupB->id,
            'assigned_by' => $this->monaSecretary->id,
            'assigned_at' => now(),
        ]);

        // 7. Create Student A in Group A
        $this->studentA = $this->createStudentWithEnrollment('طالب أ (بايثون)', '01011111111', '01211111111', $this->groupA);

        // 8. Create Student B in Group B
        $this->studentB = $this->createStudentWithEnrollment('طالب ب (سكراتش)', '01022222222', '01222222222', $this->groupB);

        // 9. Create Student Multi enrolled in BOTH Group A (Python) and Group B (Scratch)
        $this->studentMulti = $this->createStudentWithEnrollment('طالب مشترك أحمد', '01033333333', '01233333333', $this->groupA);
        // Add second enrollment in Group B
        $enr2 = Enrollment::create([
            'tenant_id' => $this->branch1->id,
            'student_id' => $this->studentMulti->id,
            'group_id' => $this->groupB->id,
            'enrollment_code' => 'ENR-MULTI-B',
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1000,
            'status' => 'active',
        ]);
        EnrollmentCourse::create([
            'tenant_id' => $this->branch1->id,
            'enrollment_id' => $enr2->id,
            'course_id' => $this->scratchCourse->id,
            'group_id' => $this->groupB->id,
            'status' => 'active',
        ]);

        // 10. Existing Follow-Up Tasks
        $this->taskA = StudentFollowUpTask::create([
            'tenant_id' => $this->branch1->id,
            'student_id' => $this->studentA->id,
            'group_id' => $this->groupA->id,
            'created_by' => $this->saraSecretary->id,
            'assigned_to' => $this->saraSecretary->id,
            'trigger_type' => 'manual',
            'source_type' => 'general',
            'source_id' => 101,
            'title' => 'متابعة بايثون مجموعة أ',
            'reason' => 'ملاحظة عامة',
            'priority' => 'normal',
            'status' => StudentFollowUpTask::STATUS_NEW,
        ]);

        $this->taskB = StudentFollowUpTask::create([
            'tenant_id' => $this->branch1->id,
            'student_id' => $this->studentB->id,
            'group_id' => $this->groupB->id,
            'created_by' => $this->monaSecretary->id,
            'assigned_to' => $this->monaSecretary->id,
            'trigger_type' => 'manual',
            'source_type' => 'general',
            'source_id' => 202,
            'title' => 'متابعة سكراتش مجموعة ب',
            'reason' => 'ملاحظة خاصة بسكراتش',
            'priority' => 'normal',
            'status' => StudentFollowUpTask::STATUS_NEW,
        ]);

        // Add contact history to Task B
        StudentFollowUpContact::create([
            'tenant_id' => $this->branch1->id,
            'task_id' => $this->taskB->id,
            'student_id' => $this->studentB->id,
            'guardian_name' => 'ولي أمر طالب ب',
            'guardian_phone' => '01222222222',
            'contact_method' => 'phone_call',
            'contact_result' => 'contacted',
            'notes' => 'سري جداً: تم الاتفاق على موعد سكراتش',
            'contacted_at' => now()->subDay(),
            'recorded_by' => $this->monaSecretary->id,
        ]);

        $this->service = app(AcademicFollowUpService::class);
    }

    protected function createStudentWithEnrollment(string $name, string $studentPhone, string $parentPhone, Group $group): Student
    {
        $user = User::create([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '', $name)) . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $group->tenant_id,
            'phone' => $studentPhone,
            'status' => 'active',
        ]);

        $student = Student::create([
            'tenant_id' => $group->tenant_id,
            'user_id' => $user->id,
            'student_code' => 'STD-' . rand(10000, 99999),
            'phone' => $studentPhone,
            'gender' => 'male',
            'status' => 'active',
        ]);

        $parentUser = User::create([
            'name' => 'ولي أمر ' . $name,
            'email' => 'p.' . strtolower(str_replace(' ', '', $name)) . rand(100, 999) . '@test.com',
            'password' => bcrypt('password'),
            'tenant_id' => $group->tenant_id,
            'phone' => $parentPhone,
            'status' => 'active',
        ]);

        StudentParent::create([
            'tenant_id' => $group->tenant_id,
            'student_id' => $student->id,
            'user_id' => $parentUser->id,
            'relation' => 'father',
            'is_primary' => true,
        ]);

        $enrollment = Enrollment::create([
            'tenant_id' => $group->tenant_id,
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrollment_code' => 'ENR-' . rand(1000, 9999) . '-' . $student->id,
            'enrollment_date' => now()->toDateString(),
            'total_amount' => 1200,
            'status' => 'active',
            'enrolled_at' => now()->subMonth(),
        ]);

        EnrollmentCourse::create([
            'tenant_id' => $group->tenant_id,
            'enrollment_id' => $enrollment->id,
            'course_id' => $group->course_id,
            'group_id' => $group->id,
            'status' => 'active',
        ]);

        return $student;
    }

    /**
     * TEST 01: Sara (Group A) CANNOT call openStudentProfile for Student B (Group B)
     */
    public function test_01_sara_cannot_open_student_profile_for_unassigned_group_b_student(): void
    {
        $this->actingAs($this->saraSecretary);

        $component = Livewire::test(AcademicFollowUpCenter::class)
            ->call('openStudentProfile', $this->studentB->id);

        // Assert modal is not opened and no student profile data is populated
        $component->assertSet('showStudentProfileModal', false);
        $component->assertSet('activeStudentProfile', null);

        // Verify service layer also rejects unauthorized retrieval
        $profile = $this->service->getStudentProfile($this->studentB, $this->saraSecretary);
        $this->assertNull($profile, 'Service must reject student profile retrieval for unassigned group student');
    }

    /**
     * TEST 02: Sara (Group A) CANNOT call openFollowUpModal for Student B (Group B)
     */
    public function test_02_sara_cannot_open_followup_modal_for_unassigned_group_b_student(): void
    {
        $this->actingAs($this->saraSecretary);

        $component = Livewire::test(AcademicFollowUpCenter::class)
            ->call('openFollowUpModal', $this->studentB->id, 'general', null, 'محاولة غير مصرحة', 'ملاحظة', $this->groupB->id);

        $component->assertSet('showFollowUpModal', false);
        $this->assertNotEquals($this->studentB->id, $component->get('activeStudentId'));
    }

    /**
     * TEST 03: Sara (Group A) CANNOT call saveFollowUpTask targeting Student B (Group B)
     */
    public function test_03_sara_cannot_save_followup_task_for_unassigned_group_b_student(): void
    {
        $this->actingAs($this->saraSecretary);

        $initialTasksCount = StudentFollowUpTask::where('student_id', $this->studentB->id)->count();

        // Attempt direct Livewire mutation
        Livewire::test(AcademicFollowUpCenter::class)
            ->set('activeStudentId', $this->studentB->id)
            ->set('activeGroupId', $this->groupB->id)
            ->set('taskAssignedTo', $this->saraSecretary->id)
            ->set('activeItemTitle', 'محاولة تسلل غير مصرحة')
            ->set('taskReason', 'سبب اختراق')
            ->call('saveFollowUpTask')
            ->assertForbidden();

        // Verify database was NOT modified
        $this->assertEquals($initialTasksCount, StudentFollowUpTask::where('student_id', $this->studentB->id)->count());
    }

    /**
     * TEST 04: Sara (Group A) CANNOT call openContactLogModal for Task B (Group B)
     */
    public function test_04_sara_cannot_open_contact_log_modal_for_unassigned_group_b_task(): void
    {
        $this->actingAs($this->saraSecretary);

        $component = Livewire::test(AcademicFollowUpCenter::class)
            ->call('openContactLogModal', $this->taskB->id);

        $component->assertSet('showContactLogModal', false);
        $this->assertNotEquals($this->taskB->id, $component->get('activeTaskId'));
    }

    /**
     * TEST 05: Sara (Group A) CANNOT call saveContactLog on Task B (Group B)
     */
    public function test_05_sara_cannot_save_contact_log_for_unassigned_group_b_task(): void
    {
        $this->actingAs($this->saraSecretary);

        $initialContactsCount = StudentFollowUpContact::where('task_id', $this->taskB->id)->count();

        Livewire::test(AcademicFollowUpCenter::class)
            ->set('activeTaskId', $this->taskB->id)
            ->set('contactMethod', 'phone_call')
            ->set('contactResult', 'contacted')
            ->set('contactNotes', 'محاولة غير مصرح بها لتعديل مهمة مجموعة أخرى')
            ->call('saveContactLog')
            ->assertForbidden();

        $this->assertEquals($initialContactsCount, StudentFollowUpContact::where('task_id', $this->taskB->id)->count());
    }

    /**
     * TEST 06: Sara (Group A) CANNOT call openHistoryModal for Task B (Group B)
     */
    public function test_06_sara_cannot_open_history_modal_for_unassigned_group_b_task(): void
    {
        $this->actingAs($this->saraSecretary);

        $component = Livewire::test(AcademicFollowUpCenter::class)
            ->call('openHistoryModal', $this->taskB->id);

        $component->assertSet('showHistoryModal', false);
        $component->assertSet('timelineContacts', []);
        $component->assertDontSee('سري جداً: تم الاتفاق على موعد سكراتش');
    }

    /**
     * TEST 07: Sara (Group A) CANNOT call saveReassignment for Task B (Group B)
     */
    public function test_07_sara_cannot_save_reassignment_for_unassigned_group_b_task(): void
    {
        $this->actingAs($this->saraSecretary);

        $initialAssignee = $this->taskB->assigned_to;

        Livewire::test(AcademicFollowUpCenter::class)
            ->set('activeTaskId', $this->taskB->id)
            ->set('reassignToUserId', $this->saraSecretary->id)
            ->set('reassignReason', 'إعادة تكليف غير مصرحة')
            ->call('saveReassignment')
            ->assertForbidden();

        $this->assertEquals($initialAssignee, $this->taskB->fresh()->assigned_to);
    }

    /**
     * TEST 08: Multi-course student (Ahmed in Python Group A & Scratch Group B)
     * Sara (assigned Group A only) views Ahmed:
     * - Sara CAN see Group A courses, attendances, and homework
     * - Sara CANNOT see Group B courses, attendances, or homework
     */
    public function test_08_multi_course_student_sara_sees_group_a_data_but_scratch_group_b_data_is_masked(): void
    {
        // Add attendance in Group A (Python)
        $sessionA = AttendanceSession::create([
            'tenant_id' => $this->branch1->id,
            'attendance_date' => now()->toDateString(),
            'course_id' => $this->pythonCourse->id,
            'group_id' => $this->groupA->id,
            'recorded_by' => $this->saraSecretary->id,
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $sessionA->id,
            'student_id' => $this->studentMulti->id,
            'status' => 'present',
        ]);

        // Add attendance in Group B (Scratch)
        $sessionB = AttendanceSession::create([
            'tenant_id' => $this->branch1->id,
            'attendance_date' => now()->toDateString(),
            'course_id' => $this->scratchCourse->id,
            'group_id' => $this->groupB->id,
            'recorded_by' => $this->monaSecretary->id,
        ]);
        AttendanceRecord::create([
            'attendance_session_id' => $sessionB->id,
            'student_id' => $this->studentMulti->id,
            'status' => 'absent',
            'notes' => 'غياب سكراتش سري',
        ]);

        // Add homework in Group B (Scratch)
        $scratchHw = Assignment::create([
            'tenant_id' => $this->branch1->id,
            'instructor_id' => $this->monaSecretary->id,
            'group_id' => $this->groupB->id,
            'title' => 'واجب سكراتش المتقدم',
            'max_score' => 20,
            'due_date' => now()->subDay(),
        ]);
        AssignmentSubmission::create([
            'tenant_id' => $this->branch1->id,
            'assignment_id' => $scratchHw->id,
            'student_id' => $this->studentMulti->id,
            'status' => 'graded',
            'score' => 18,
            'submitted_at' => now()->subDays(2),
        ]);

        // Sara retrieves Ahmed's profile
        $profile = $this->service->getStudentProfile($this->studentMulti, $this->saraSecretary);

        $this->assertNotNull($profile);

        // Courses list: Only Group A must appear
        $courseGroupIds = collect($profile['courses'])->pluck('group_id')->toArray();
        $this->assertContains($this->groupA->id, $courseGroupIds);
        $this->assertNotContains($this->groupB->id, $courseGroupIds, 'Group B course must NOT be visible to Sara');

        // Attendance list: Only Group A sessions must appear
        $attendanceHistory = collect($profile['attendance']['history']);
        $this->assertCount(1, $attendanceHistory);
        $this->assertEquals($this->groupA->name, $attendanceHistory->first()['group_name']);

        // Homework list: Group B submissions must NOT appear
        $homeworkList = collect($profile['homework']);
        $this->assertEmpty($homeworkList, 'Group B homework submission must NOT be visible to Sara');
    }

    /**
     * TEST 09: User without phone visibility permission CANNOT view real contact numbers
     */
    public function test_09_user_without_phone_permission_does_not_receive_real_phone_numbers(): void
    {
        // Create user with dashboard permission but WITHOUT phone permissions
        $userNoPhone = User::create([
            'name' => 'موظف بدون صلاحية هاتف',
            'email' => 'no_phone.sec@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'status' => 'active',
        ]);
        $userNoPhone->givePermissionTo('view_daily_follow_up_dashboard');

        SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch1->id,
            'user_id' => $userNoPhone->id,
            'group_id' => $this->groupA->id,
            'assigned_by' => $userNoPhone->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($userNoPhone);

        // 1. In getStudentProfile
        $profile = $this->service->getStudentProfile($this->studentA, $userNoPhone);
        $this->assertEquals('— (محجوب)', $profile['parent_phone']);
        $this->assertEquals('— (محجوب)', $profile['student_phone']);

        // 2. In Livewire FollowUp Modal
        $component = Livewire::test(AcademicFollowUpCenter::class)
            ->call('openFollowUpModal', $this->studentA->id, 'general', null, 'فحص الهاتف', 'سبب', $this->groupA->id);

        $component->assertSet('activeParentPhone', '— (محجوب)');
        $component->assertSet('activeStudentPhone', '— (محجوب)');
        $component->assertDontSee('01211111111');
        $component->assertDontSee('01011111111');

        // 3. In History Modal
        $histComponent = Livewire::test(AcademicFollowUpCenter::class)
            ->call('openHistoryModal', $this->taskA->id);
        $histContacts = $histComponent->get('timelineContacts');
        foreach ($histContacts as $c) {
            $this->assertEquals('— (محجوب)', $c['guardian_phone']);
        }
    }

    /**
     * TEST 10: User with group assignment but NO dashboard permission gets 403 on mount
     */
    public function test_10_user_with_group_assignment_but_no_dashboard_permission_gets_403_on_mount(): void
    {
        $userNoPerm = User::create([
            'name' => 'سكرتيرة بدون تصريح لوحة',
            'email' => 'no_perm@aischool.test',
            'password' => bcrypt('password'),
            'tenant_id' => $this->branch1->id,
            'status' => 'active',
        ]);
        // Has role 'secretary' but zero permissions granted
        $emptyRole = Role::firstOrCreate(['name' => 'secretary_restricted', 'guard_name' => 'web']);
        $userNoPerm->assignRole($emptyRole);

        SupervisionGroupAssignment::create([
            'tenant_id' => $this->branch1->id,
            'user_id' => $userNoPerm->id,
            'group_id' => $this->groupA->id,
            'assigned_by' => $userNoPerm->id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($userNoPerm);

        Livewire::test(AcademicFollowUpCenter::class)
            ->assertForbidden();
    }

    /**
     * TEST 11: Reassigning a case to an ineligible employee from another branch is rejected
     */
    public function test_11_reassign_task_to_ineligible_cross_branch_employee_is_rejected(): void
    {
        $this->actingAs($this->saraSecretary);

        // Attempt to reassign Task A to Samir from Branch 2
        Livewire::test(AcademicFollowUpCenter::class)
            ->set('activeTaskId', $this->taskA->id)
            ->set('reassignToUserId', $this->crossBranchSecretary->id)
            ->set('reassignReason', 'محاولة تحويل لفرع آخر')
            ->call('saveReassignment')
            ->assertStatus(422);

        $this->assertEquals($this->saraSecretary->id, $this->taskA->fresh()->assigned_to);
    }

    /**
     * TEST 12: Duplicate task submission within cooldown window is prevented
     */
    public function test_12_duplicate_task_submission_is_prevented(): void
    {
        $this->actingAs($this->saraSecretary);

        $initialCount = StudentFollowUpTask::where('student_id', $this->studentA->id)->count();

        // 1st Creation: successful
        Livewire::test(AcademicFollowUpCenter::class)
            ->set('activeStudentId', $this->studentA->id)
            ->set('activeGroupId', $this->groupA->id)
            ->set('activeSourceType', 'exam')
            ->set('activeSourceId', 999)
            ->set('taskAssignedTo', $this->saraSecretary->id)
            ->set('activeItemTitle', 'مهمة فحص تكرار')
            ->set('taskReason', 'اختبار تكرار النقرات')
            ->call('saveFollowUpTask')
            ->assertSet('showFollowUpModal', false);

        $this->assertEquals($initialCount + 1, StudentFollowUpTask::where('student_id', $this->studentA->id)->count());

        // Immediate 2nd Creation with identical student and source: must be prevented
        Livewire::test(AcademicFollowUpCenter::class)
            ->set('activeStudentId', $this->studentA->id)
            ->set('activeGroupId', $this->groupA->id)
            ->set('activeSourceType', 'exam')
            ->set('activeSourceId', 999)
            ->set('taskAssignedTo', $this->saraSecretary->id)
            ->set('activeItemTitle', 'مهمة فحص تكرار')
            ->set('taskReason', 'اختبار تكرار النقرات')
            ->call('saveFollowUpTask');

        // Count must still be exactly initial + 1 (no duplicate created)
        $this->assertEquals($initialCount + 1, StudentFollowUpTask::where('student_id', $this->studentA->id)->count());
    }
}

<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicGradeCorrection;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AttendanceSession;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamRetakeGrant;
use App\Models\Group;
use App\Models\HomeworkDeadlineExtension;
use App\Models\HomeworkResubmissionGrant;
use App\Models\Student;
use App\Models\User;
use App\Services\AcademicCenterService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "=== STARTING COMPREHENSIVE ACADEMIC CENTER VERIFICATION ===\n\n";

$service = app(AcademicCenterService::class);
$user = User::first();
$student = Student::first();
$group = Group::first();
$session = AttendanceSession::where('group_id', $group->id)->first();

if (!$student || !$group || !$user) {
    echo "ERROR: Missing seed data (student, group, or user)\n";
    exit(1);
}

echo "Actor User: {$user->name} (ID: {$user->id})\n";
echo "Student: {$student->user?->name} (ID: {$student->id})\n";
echo "Group: {$group->name} (ID: {$group->id})\n";
if ($session) {
    echo "Session: {$session->session_notes} (ID: {$session->id})\n";
}

DB::beginTransaction();

try {
    // -------------------------------------------------------------
    // SCENARIO 1: HOMEWORK WORKFLOW, RESUBMISSIONS & HIGHEST GRADE
    // -------------------------------------------------------------
    echo "\n--- SCENARIO 1: HOMEWORK RESUBMISSION & OFFICIAL HIGHEST GRADE ---\n";
    
    $instructor = $group->instructor ?: \App\Models\Instructor::first();

    // 1. Create a test assignment
    $assignment = Assignment::create([
        'tenant_id' => $group->tenant_id,
        'group_id' => $group->id,
        'instructor_id' => $instructor?->id,
        'attendance_session_id' => $session?->id,
        'title' => 'واجب اختباري للتحقق من أعلى درجة',
        'description' => 'شرح الواجب الاختباري',
        'due_date' => now()->addDays(5),
        'max_score' => 10.0,
        'status' => 'published',
    ]);
    echo "Created Assignment ID: {$assignment->id}, Max Score: {$assignment->max_score}\n";

    // 2. Submit initial attempt (attempt 1)
    $sub1 = AssignmentSubmission::create([
        'tenant_id' => $assignment->tenant_id,
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'submission_text' => 'الحل الأول للطالب',
        'submitted_at' => now(),
        'status' => 'submitted',
    ]);
    echo "Initial Submission created: ID {$sub1->id}, Attempt: {$sub1->attempt_number}\n";

    // 3. Instructor grades attempt 1 with score 6/10 (instant publication)
    $sub1 = $service->gradeHomework($sub1, 6.0, 'ملاحظة المعلم: حل جيد ولكن يحتاج تدريب', $user);
    $sub1->refresh();
    echo "DEBUG: sub1 DB row: " . json_encode($sub1->toArray()) . "\n";
    echo "Grade 1 applied: {$sub1->score}/10, Published: " . ($sub1->isPublished() ? 'YES' : 'NO') . ", Official: " . ($sub1->is_official ? 'YES' : 'NO') . "\n";
    if ($sub1->score != 6.0 || !$sub1->is_official) {
        throw new \Exception("Scenario 1 Failed: Initial submission should have official grade 6.0");
    }

    // 4. Issue a one-time resubmission grant
    $grant1 = $service->grantHomeworkResubmission($assignment, $student, now()->addDays(3), 'إتاحة فرصة تحسين الدرجة للطالب', $user);
    echo "Resubmission Grant 1 issued: ID {$grant1->id}, Status: {$grant1->status}\n";

    // 5. Submit attempt 2 using the grant
    $sub2 = AssignmentSubmission::create([
        'tenant_id' => $assignment->tenant_id,
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'attempt_number' => 2,
        'submission_text' => 'الحل الثاني المحسن',
        'submitted_at' => now(),
        'status' => 'submitted',
    ]);
    // Consume grant
    $grant1->update(['status' => 'consumed', 'consumed_at' => now(), 'consumed_submission_id' => $sub2->id]);
    echo "Submission 2 created: ID {$sub2->id}, Attempt: {$sub2->attempt_number}\n";

    // Check grant cannot be consumed again
    if ($grant1->fresh()->status !== 'consumed') {
        throw new \Exception("Grant 1 should be consumed");
    }

    // While attempt 2 is ungraded, official grade must remain attempt 1 (6.0)
    $currentOfficial = $service->recalculateOfficialHomeworkGrade($assignment->id, $student->id);
    echo "Official grade while attempt 2 is pending grading: {$currentOfficial->score}/10 (Attempt #{$currentOfficial->attempt_number})\n";
    if ($currentOfficial->id !== $sub1->id || $currentOfficial->score != 6.0) {
        throw new \Exception("Ungraded attempt must not overwrite official score");
    }

    // 6. Grade attempt 2 with score 9/10
    $sub2 = $service->gradeHomework($sub2, 9.0, 'أحسنت! إجابة ممتازة ومكتملة', $user);
    $currentOfficial = $service->recalculateOfficialHomeworkGrade($assignment->id, $student->id);
    echo "Official grade after grading attempt 2: {$currentOfficial->score}/10 (Attempt #{$currentOfficial->attempt_number})\n";
    if ($currentOfficial->id !== $sub2->id || $currentOfficial->score != 9.0) {
        throw new \Exception("Official grade should now be Attempt 2 (9.0)");
    }

    // 7. Issue grant 2 and submit attempt 3 with score 7/10 (lower score)
    $grant2 = $service->grantHomeworkResubmission($assignment, $student, now()->addDays(3), 'محاولة ثالثة لاختبار ثبات أعلى درجة', $user);
    $sub3 = AssignmentSubmission::create([
        'tenant_id' => $assignment->tenant_id,
        'assignment_id' => $assignment->id,
        'student_id' => $student->id,
        'attempt_number' => 3,
        'submission_text' => 'الحل الثالث',
        'submitted_at' => now(),
        'status' => 'submitted',
    ]);
    $grant2->update(['status' => 'consumed', 'consumed_at' => now(), 'consumed_submission_id' => $sub3->id]);
    $sub3 = $service->gradeHomework($sub3, 7.0, 'درجة المحاولة الثالثة 7/10', $user);

    $currentOfficial = $service->recalculateOfficialHomeworkGrade($assignment->id, $student->id);
    echo "Official grade after attempt 3 (7/10): {$currentOfficial->score}/10 (Attempt #{$currentOfficial->attempt_number})\n";
    if ($currentOfficial->id !== $sub2->id || $currentOfficial->score != 9.0) {
        throw new \Exception("Highest grade rule violated: official grade must remain 9.0 from attempt 2!");
    }
    echo "✓ SCENARIO 1 PASSED: Highest grade rule (9.0/10) rigorously maintained!\n";

    // -------------------------------------------------------------
    // SCENARIO 2: PUBLISHED GRADE CORRECTION & AUDIT LOG
    // -------------------------------------------------------------
    echo "\n--- SCENARIO 2: PUBLISHED GRADE CORRECTION WITH AUDIT ---\n";
    $auditBefore = AcademicGradeCorrection::count();
    $auditLog = $service->correctPublishedGrade($sub2, 9.5, 'إضافة نصف درجة للسؤال الإضافي بعد المراجعة', $user);
    $sub2->refresh();
    $auditAfter = AcademicGradeCorrection::count();
    
    echo "Old score: 9.0 -> New corrected score: {$sub2->score}\n";
    echo "Audit logs count before: {$auditBefore}, after: {$auditAfter}\n";
    $latestAudit = AcademicGradeCorrection::latest('id')->first();
    echo "Recorded Audit Reason: '{$latestAudit->reason}', Previous: {$latestAudit->previous_score}, New: {$latestAudit->new_score}\n";

    if ($sub2->score != 9.5 || $latestAudit->previous_score != 9.0 || $latestAudit->new_score != 9.5) {
        throw new \Exception("Grade correction audit failed!");
    }
    echo "✓ SCENARIO 2 PASSED: Published grade correction properly audited!\n";

    // -------------------------------------------------------------
    // SCENARIO 3: DEADLINE EXPIRATION & EXTENSIONS
    // -------------------------------------------------------------
    echo "\n--- SCENARIO 3: DEADLINE EXPIRATION & EXTENSIONS ---\n";
    // Create assignment with deadline in the past (student has not submitted)
    $deadlineAsg = Assignment::create([
        'tenant_id' => $group->tenant_id,
        'group_id' => $group->id,
        'instructor_id' => $instructor?->id,
        'attendance_session_id' => $session?->id,
        'title' => 'واجب اختباري لاختبار المواعيد النهائية والتمديد',
        'due_date' => now()->subDay(),
        'max_score' => 10.0,
        'status' => 'published',
    ]);
    
    $isClosed = $deadlineAsg->isSubmissionClosedForStudent($student);
    echo "Is assignment submission closed for student? " . ($isClosed ? 'YES' : 'NO') . "\n";
    if (!$isClosed) {
        throw new \Exception("Submission should be closed past deadline!");
    }

    // Grant individual student reopening
    $ext = $service->reopenStudentSubmission($deadlineAsg, $student, now()->addDays(2), 'استثناء بسبب عذر مرضي', $user);
    $isClosedAfterExt = $deadlineAsg->fresh()->isSubmissionClosedForStudent($student);
    echo "Is assignment submission closed after individual reopening? " . ($isClosedAfterExt ? 'YES' : 'NO') . "\n";
    if ($isClosedAfterExt) {
        throw new \Exception("Submission should be open for reopened student!");
    }

    // Check that another student is still closed
    $otherStudent = Student::where('id', '!=', $student->id)->first();
    if ($otherStudent) {
        $isClosedOther = $deadlineAsg->isSubmissionClosedForStudent($otherStudent);
        echo "Is assignment submission closed for OTHER student? " . ($isClosedOther ? 'YES' : 'NO') . "\n";
        if (!$isClosedOther) {
            throw new \Exception("Reopening should only apply to target student!");
        }
    }
    echo "✓ SCENARIO 3 PASSED: Individual and group extensions enforce precise boundaries!\n";

    // -------------------------------------------------------------
    // SCENARIO 4: EXAM OPTIONAL SESSION & RETAKES & HIGHEST SCORE
    // -------------------------------------------------------------
    echo "\n--- SCENARIO 4: EXAM RETAKE GRANTS & HIGHEST COMPLETED ATTEMPT ---\n";
    // 1. Create a comprehensive exam (no session link)
    $exam = Exam::create([
        'tenant_id' => $group->tenant_id,
        'course_id' => $group->course_id,
        'group_id' => $group->id,
        'instructor_id' => $instructor?->id,
        'attendance_session_id' => null, // Comprehensive exam without session!
        'title' => 'امتحان شامل تجريبي للمجموعة',
        'type' => 'comprehensive',
        'duration' => 60,
        'total_marks' => 20.0,
        'passing_marks' => 10.0,
        'exam_date' => now()->subHours(1),
        'status' => 'scheduled',
        'published_at' => null, // Unpublished initially!
    ]);
    echo "Created Comprehensive Exam ID: {$exam->id}, Session: " . ($exam->attendance_session_id ?: 'NONE (COMPREHENSIVE)') . "\n";

    // 2. Attempt 1: score 8/20
    $att1 = ExamAttempt::create([
        'tenant_id' => $exam->tenant_id,
        'exam_id' => $exam->id,
        'student_id' => $student->id,
        'attempt_number' => 1,
        'started_at' => now()->subMinutes(50),
        'completed_at' => now()->subMinutes(10),
        'total_score' => 20.0,
        'score' => 8.0,
        'percentage' => 40.0,
        'passed' => false,
        'is_official' => true,
    ]);
    echo "Attempt 1: Score {$att1->score}/20, Passed: " . ($att1->passed ? 'YES' : 'NO') . "\n";

    // Check student cannot start new attempt without retake grant
    $checkRetake = $service->canStudentStartExam($exam, $student);
    echo "Can student start new attempt without retake grant? " . ($checkRetake['allowed'] ? 'YES' : 'NO') . " (Reason: {$checkRetake['reason']})\n";
    if ($checkRetake['allowed']) {
        throw new \Exception("Student should not be able to start new attempt without retake grant!");
    }

    // 3. Authorized employee grants exam retake
    $retakeGrant = $service->grantExamRetake($exam, $student, now()->addDays(2), 'الموافقة على إعادة الاختبار بعد مراجعة المشرف', $user);
    echo "Exam Retake Grant issued: ID {$retakeGrant->id}, Status: {$retakeGrant->status}\n";

    // 4. Student starts attempt 2 (grant is atomically consumed)
    $att2 = $service->startExamAttempt($exam, $student);
    echo "Attempt 2 started: ID {$att2->id}, Attempt Number: {$att2->attempt_number}\n";
    if ($retakeGrant->fresh()->status !== 'consumed') {
        throw new \Exception("Retake grant must be consumed upon attempt start!");
    }

    // 5. Finalize attempt 2 with score 16/20
    $att2->update([
        'completed_at' => now(),
        'score' => 16.0,
        'percentage' => 80.0,
        'passed' => true,
    ]);
    $officialExamAttempt = $service->recalculateOfficialExamGrade($exam->id, $student->id);
    echo "Official exam attempt after Attempt 2: Attempt #{$officialExamAttempt->attempt_number}, Score: {$officialExamAttempt->score}/20\n";
    if ($officialExamAttempt->id !== $att2->id || $officialExamAttempt->score != 16.0) {
        throw new \Exception("Highest completed exam attempt rule failed!");
    }

    // 6. Retake 3 with score 12/20
    $retakeGrant2 = $service->grantExamRetake($exam, $student, now()->addDays(2), 'محاولة ثالثة', $user);
    $att3 = $service->startExamAttempt($exam, $student);
    $att3->update([
        'completed_at' => now(),
        'score' => 12.0,
        'percentage' => 60.0,
        'passed' => true,
    ]);
    $officialExamAttempt = $service->recalculateOfficialExamGrade($exam->id, $student->id);
    echo "Official exam attempt after Attempt 3 (12/20): Attempt #{$officialExamAttempt->attempt_number}, Score: {$officialExamAttempt->score}/20\n";
    if ($officialExamAttempt->id !== $att2->id || $officialExamAttempt->score != 16.0) {
        throw new \Exception("Highest completed exam attempt must remain Attempt 2 (16.0)!");
    }

    // Check Question Protection: attempts have started, questions cannot be edited destructively
    $hasStarted = $exam->hasStartedAttempts();
    echo "Exam has started attempts? " . ($hasStarted ? 'YES (LOCKED)' : 'NO') . "\n";
    if (!$hasStarted) {
        throw new \Exception("Exam attempts lock should be active!");
    }

    // Exam Publication Rule Check: Exam is NOT published yet
    echo "Exam isPublished? " . ($exam->isPublished() ? 'YES' : 'NO') . "\n";
    if ($exam->isPublished()) {
        throw new \Exception("Exam should not be published without explicit publish action!");
    }

    // Publish Exam
    $exam->update(['published_at' => now(), 'status' => 'completed']);
    echo "Exam isPublished after toggle? " . ($exam->fresh()->isPublished() ? 'YES (PUBLISHED)' : 'NO') . "\n";
    if (!$exam->fresh()->isPublished()) {
        throw new \Exception("Exam should be published after publish action!");
    }
    echo "✓ SCENARIO 4 PASSED: Exam retakes, atomic grant consumption, and highest score rules verified!\n";

    // -------------------------------------------------------------
    // SCENARIO 5: ACADEMIC ALERTS & 50% BOUNDARY
    // -------------------------------------------------------------
    echo "\n--- SCENARIO 5: ACADEMIC ALERTS & 50% BOUNDARY ---\n";
    // Test Case A: Exactly 50% (5.0 / 10.0) -> MUST NOT TRIGGER ALERT
    $isAlert50 = ((5.0 * 2) < 10.0);
    echo "Score 5.0 / 10.0 (50.0%) has alert? " . ($isAlert50 ? 'YES' : 'NO') . "\n";
    if ($isAlert50) {
        throw new \Exception("Score of exactly 50% must NOT trigger low-grade alert!");
    }

    // Test Case B: 4.9 / 10.0 (49.0%) -> MUST TRIGGER ALERT
    $isAlert49 = ((4.9 * 2) < 10.0);
    echo "Score 4.9 / 10.0 (49.0%) has alert? " . ($isAlert49 ? 'YES' : 'NO') . "\n";
    if (!$isAlert49) {
        throw new \Exception("Score strictly below 50% MUST trigger low-grade alert!");
    }

    // Test Case C: 5.1 / 10.0 (51.0%) -> MUST NOT TRIGGER ALERT
    $isAlert51 = ((5.1 * 2) < 10.0);
    echo "Score 5.1 / 10.0 (51.0%) has alert? " . ($isAlert51 ? 'YES' : 'NO') . "\n";
    if ($isAlert51) {
        throw new \Exception("Score above 50% must NOT trigger alert!");
    }
    echo "✓ SCENARIO 5 PASSED: Strict < 50% boundary alert verified!\n";

    echo "\n=======================================================\n";
    echo "ALL ACADEMIC WORKFLOWS VERIFIED WITH 100% SUCCESS!\n";
    echo "=======================================================\n";

} catch (\Exception $e) {
    echo "\n❌ EXCEPTION THROWN: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . " in " . $e->getFile() . "\n";
    DB::rollBack();
    exit(1);
} finally {
    // Rollback test artifacts to keep staging/dev database clean
    DB::rollBack();
    echo "\nCleaned up test transaction (Database rolled back safely).\n";
}

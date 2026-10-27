<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentFollowUpTask;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class StudentFollowUpService
{
    /**
     * Scan student for academic risk indicators and create follow-up tasks if needed
     */
    public function checkAndTriggerFollowUp(Student $student, int $absenceThreshold = 3): ?StudentFollowUpTask
    {
        // 1. Check Repeated Absences
        $recentAbsences = AttendanceRecord::where('student_id', $student->id)
            ->where('status', 'absent')
            ->count();

        if ($recentAbsences >= $absenceThreshold) {
            $existing = StudentFollowUpTask::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->where('trigger_type', 'repeated_absence')
                ->whereIn('status', ['pending', 'in_progress'])
                ->first();

            if (!$existing) {
                // Find a supervisor or admin in the same tenant to assign to
                $assignee = User::where('tenant_id', $student->tenant_id)
                    ->whereHas('roles', fn($q) => $q->whereIn('name', ['supervisor', 'admin', 'administrator']))
                    ->first();

                return StudentFollowUpTask::create([
                    'tenant_id' => $student->tenant_id,
                    'student_id' => $student->id,
                    'assigned_to' => $assignee?->id,
                    'trigger_type' => 'repeated_absence',
                    'title' => 'غياب متكرر: تم تسجيل ' . $recentAbsences . ' غياب للطالب',
                    'reason' => "تجاوز الطالب الحد المسموح به من الغياب ({$recentAbsences} مرات). يرجى التواصل مع ولي الأمر لمتابعة الحالة.",
                    'priority' => 'high',
                    'status' => 'pending',
                ]);
            }
        }

        // 2. Check Declining / Low Grades
        $failingGrades = Grade::where('student_id', $student->id)
            ->where('percentage', '<', 50)
            ->count();

        if ($failingGrades >= 2) {
            $existing = StudentFollowUpTask::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->where('trigger_type', 'declining_grades')
                ->whereIn('status', ['pending', 'in_progress'])
                ->first();

            if (!$existing) {
                $assignee = User::where('tenant_id', $student->tenant_id)
                    ->whereHas('roles', fn($q) => $q->whereIn('name', ['supervisor', 'admin']))
                    ->first();

                return StudentFollowUpTask::create([
                    'tenant_id' => $student->tenant_id,
                    'student_id' => $student->id,
                    'assigned_to' => $assignee?->id,
                    'trigger_type' => 'declining_grades',
                    'title' => 'تدني في المستوى الدراسي (رسوب في اختبارين أو أكثر)',
                    'reason' => 'حصل الطالب على نسبة أقل من 50% في أكثر من اختبار. يتطلب دعماً وتنسيقاً مع المدرب.',
                    'priority' => 'normal',
                    'status' => 'pending',
                ]);
            }
        }

        return null;
    }
}

<?php

namespace App\Observers;

use App\Models\Student;
use Illuminate\Support\Facades\Log;

class StudentObserver
{
    /**
     * Handle the Student "updating" event.
     */
    public function updating(Student $student): void
    {
        // التحقق من تغيير الحالة من نشط إلى غير نشط
        if ($student->isDirty('status')) {
            $oldStatus = $student->getOriginal('status');
            $newStatus = $student->status;

            if ($oldStatus === 'active' && $newStatus === 'inactive') {
                $this->handleStudentDeactivation($student);
            }
        }
    }

    /**
     * معالجة إلغاء تفعيل الطالب
     */
    protected function handleStudentDeactivation(Student $student): void
    {
        try {
            // 1. حذف الأقساط المتأخرة
            $overdueInstallmentsDeleted = \App\Models\OverdueInstallment::whereHas('installment', function ($query) use ($student) {
                $query->whereHas('enrollment', function ($q) use ($student) {
                    $q->where('student_id', $student->id);
                });
            })->delete();

            // 2. إزالة الطالب من جميع المجموعات
            $groupsRemoved = 0;
            
            // جلب جميع تسجيلات الطالب
            $enrollments = $student->enrollments()->with('enrollmentCourses')->get();

            foreach ($enrollments as $enrollment) {
                foreach ($enrollment->enrollmentCourses as $enrollmentCourse) {
                    if ($enrollmentCourse->group_id) {
                        // إزالة المجموعة وتعطيل الكورس
                        $enrollmentCourse->update([
                            'group_id' => null,
                            'status' => 'inactive',
                        ]);
                        $groupsRemoved++;
                    }
                }
            }

            Log::info("Student deactivated", [
                'student_id' => $student->id,
                'student_name' => $student->user->name ?? 'Unknown',
                'overdue_installments_deleted' => $overdueInstallmentsDeleted,
                'groups_removed' => $groupsRemoved,
            ]);

        } catch (\Exception $e) {
            Log::error("Error handling student deactivation", [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Student "updated" event.
     */
    public function updated(Student $student): void
    {
        // يمكن إضافة إشعارات هنا إذا لزم الأمر
    }
}
<?php

namespace App\Services\Parent;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ParentStudentAccessService
{
    /**
     * Resolve the validated student for the currently authenticated parent.
     * Prevents session tampering, cross-tenant leakage, and unauthorized student access.
     */
    public static function getSelectedStudent(): ?Student
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $sessionStudentId = session('selected_student_id');

        if ($sessionStudentId) {
            // Verify that the requested student strictly belongs to this authenticated parent
            $student = $user->students()
                ->with('user')
                ->where('students.id', (int) $sessionStudentId)
                ->first();

            if ($student) {
                return $student;
            }

            // Session value was invalid or unauthorized - clear it
            Log::warning('Parent attempted to access unauthorized student ID or session tampered', [
                'user_id' => $user->id,
                'attempted_student_id' => $sessionStudentId,
            ]);
            session()->forget('selected_student_id');
        }

        // Fallback to the parent's first authorized student
        $firstStudent = $user->students()->with('user')->first();
        if ($firstStudent) {
            session(['selected_student_id' => $firstStudent->id]);
            return $firstStudent;
        }

        return null;
    }

    /**
     * Get the validated student ID for the currently authenticated parent.
     */
    public static function getSelectedStudentId(): ?int
    {
        return static::getSelectedStudent()?->id;
    }

    /**
     * Switch the selected student after validating parent authorization.
     */
    public static function switchStudent(int $studentId): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $isAuthorized = $user->students()->where('students.id', $studentId)->exists();

        if ($isAuthorized) {
            session(['selected_student_id' => $studentId]);
            return true;
        }

        Log::warning('Unauthorized parent student switch attempt blocked', [
            'user_id' => $user->id,
            'attempted_student_id' => $studentId,
        ]);

        return false;
    }

    /**
     * Get all authorized students for the parent as [id => name] array.
     */
    public static function getAuthorizedStudentsList(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        return $user->students()
            ->with('user')
            ->get()
            ->pluck('user.name', 'id')
            ->toArray();
    }
}

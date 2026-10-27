<?php

namespace App\Http\Middleware;

use App\Models\Enrollment;
use App\Models\Installment;
use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOverdueInstallments
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Skip for admin and instructor
        if ($user->hasRole(['super_admin', 'admin', 'instructor'])) {
            return $next($request);
        }

        // Check for students
        if ($user->hasRole('student')) {
            $student = $user->student;
            
            if ($student && $this->hasOverdueInstallments($student->id)) {
                // Store overdue status in session
                session(['has_overdue_installments' => true]);
                
                // Allow only dashboard and profile routes
                // if (!$this->isAllowedRoute($request)) {
                //     return redirect()->route('filament.student.pages.dashboard')
                //         ->with('warning', 'لديك أقساط متأخرة. يرجى سداد الأقساط للوصول إلى كامل الخدمات.');
                // }
            } else {
                session()->forget('has_overdue_installments');
            }
        }

        // Check for parents
        if ($user->hasRole('parent')) {
            $parent = $user->studentParent;
            
            if ($parent) {
                $studentIds = Student::where('parent_id', $parent->id)->pluck('id');
                $hasOverdue = false;
                
                foreach ($studentIds as $studentId) {
                    if ($this->hasOverdueInstallments($studentId)) {
                        $hasOverdue = true;
                        break;
                    }
                }
                
                if ($hasOverdue) {
                    session(['has_overdue_installments' => true]);
                    
                    // if (!$this->isAllowedRoute($request)) {
                    //     return redirect()->route('filament.parent.pages.dashboard')
                    //         ->with('warning', 'يوجد أقساط متأخرة لأحد أبنائك. يرجى سداد الأقساط للوصول إلى كامل الخدمات.');
                    // }
                } else {
                    session()->forget('has_overdue_installments');
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if student has overdue installments
     */
    private function hasOverdueInstallments(int $studentId): bool
    {
        $enrollmentIds = Enrollment::where('student_id', $studentId)
            ->where('status', 'active')
            ->pluck('id');

        return Installment::whereIn('enrollment_id', $enrollmentIds)
            ->overdue()
            ->exists();
    }

    /**
     * Check if route is allowed for users with overdue installments
     */
    private function isAllowedRoute(Request $request): bool
    {
        $allowedRoutes = [
            'filament.student.pages.dashboard',
            'filament.parent.pages.dashboard',
            'filament.student.auth.profile',
            'filament.parent.auth.profile',
            'filament.student.auth.logout',
            'filament.parent.auth.logout',
        ];

        return in_array($request->route()?->getName(), $allowedRoutes) ||
            str_contains($request->path(), 'auth/') ||
            str_contains($request->path(), 'logout');
    }
}

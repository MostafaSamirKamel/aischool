<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfWrongPanel
{
    /**
     * Handle an incoming request.
     * Redirect user to their appropriate panel if they're in the wrong one
     */
    public function handle(Request $request, Closure $next, string $expectedPanel): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Super Admin can access any panel
        if ($user->is_super_admin) {
            return $next($request);
        }

        $correctPanel = $this->getCorrectPanel($user);

        // If user is in wrong panel, redirect to correct one
        if ($correctPanel !== $expectedPanel && $correctPanel !== null) {
            return redirect("/{$correctPanel}");
        }

        return $next($request);
    }

    /**
     * Determine the correct panel for the user based on their role
     */
    private function getCorrectPanel($user): ?string
    {
        // Super Admin, Admin, Branch Manager, Secretary, Accountant, Registration, Employee → Admin Panel
        if ($user->hasAnyRole(['super_admin', 'admin', 'branch_manager', 'secretary', 'accountant', 'registration', 'financial_employee', 'employee'])) {
            return 'admin';
        }


        // Supervisor → Supervisor Panel
        if ($user->hasRole('supervisor')) {
            return 'supervisor';
        }

        // Instructor → Instructor Panel
        if ($user->hasRole('instructor') || $user->hasRole('teacher')) {
            return 'instructor';
        }

        // Parent → Parent Panel
        if ($user->hasRole('parent')) {
            return 'parent';
        }

        // Student → Student Panel
        if ($user->hasRole('student')) {
            return 'student';
        }

        // Default to admin if no specific role
        return 'admin';
    }
}

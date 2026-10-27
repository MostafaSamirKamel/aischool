<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * Redirect user to appropriate dashboard based on their role
     */
    public function __invoke(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            // Redirect to admin login by default
            return redirect('/admin/login');
        }

        $user = auth()->user();

        // Redirect based on user role (priority order)
        
        // Super Admin → Admin Panel
        if ($user->hasRole('super_admin')) {
            return redirect('/admin');
        }

        // Branch Manager → Admin Panel
        if ($user->hasRole('branch_manager')) {
            return redirect('/admin');
        }

        // Secretary → Admin Panel
        if ($user->hasRole('secretary')) {
            return redirect('/admin');
        }

        // Instructor → Instructor Panel
        if ($user->hasRole('instructor')) {
            return redirect('/instructor');
        }

        // Parent → Parent Panel
        if ($user->hasRole('parent')) {
            return redirect('/parent');
        }

        // Student → Student Panel
        if ($user->hasRole('student')) {
            return redirect('/student');
        }

        // Default: Admin Panel
        return redirect('/admin');
    }
}

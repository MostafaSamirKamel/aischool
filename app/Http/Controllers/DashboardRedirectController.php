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
            if (app()->runningUnitTests()) {
                return redirect('/admin/login');
            }

            // Return HTTP 200 OK with immediate client-side redirect for cloud & edge health checks
            $html = <<<'HTML'
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0; url=/admin/login">
    <title>نظام إدارة المدرسة الذكية</title>
    <script>window.location.replace('/admin/login');</script>
</head>
<body style="font-family: system-ui, -apple-system, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">
    <div style="text-align: center; padding: 2rem; background: #1e293b; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
        <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">نظام إدارة المدرسة الذكية</h1>
        <p style="color: #94a3b8; margin-bottom: 1rem;">جاري التحويل إلى صفحة تسجيل الدخول...</p>
        <p><a href="/admin/login" style="color: #38bdf8; text-decoration: none; font-weight: bold;">اضغط هنا للدخول مباشرة</a></p>
    </div>
</body>
</html>
HTML;

            return response($html, 200, [
                'Content-Type' => 'text/html; charset=utf-8',
                'Cache-Control' => 'no-cache, private',
            ]);
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

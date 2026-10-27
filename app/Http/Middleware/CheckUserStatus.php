<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status !== 'active') {
            $panelId = null;
            try {
                $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
            } catch (\Throwable $e) {
                // Outside Filament context
            }

            $loginRoute = match ($panelId) {
                'instructor' => 'filament.instructor.auth.login',
                'supervisor' => 'filament.supervisor.auth.login',
                'student' => 'filament.student.auth.login',
                'parent' => 'filament.parent.auth.login',
                default => 'filament.admin.auth.login',
            };

            Auth::logout();
            
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            
            if (\Illuminate\Support\Facades\Route::has($loginRoute)) {
                return redirect()->route($loginRoute)
                    ->withErrors(['email' => __('حسابك غير نشط. يرجى التواصل مع الإدارة.')]);
            }

            return redirect('/admin/login')
                ->withErrors(['email' => __('حسابك غير نشط. يرجى التواصل مع الإدارة.')]);
        }
        
        return $next($request);
    }
}

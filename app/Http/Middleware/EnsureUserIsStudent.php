<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('filament.student.auth.login');
        }

        // Check if user has student role
        if (!$user->hasRole('student')) {
            abort(403, 'غير مصرح لك بالوصول إلى لوحة تحكم الطلاب');
        }

        return $next($request);
    }
}

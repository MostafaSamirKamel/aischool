<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsParent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('filament.parent.auth.login');
        }

        // Check if user has parent role
        if (!$user->hasRole('parent')) {
            abort(403, 'غير مصرح لك بالوصول إلى لوحة تحكم أولياء الأمور');
        }

        return $next($request);
    }
}

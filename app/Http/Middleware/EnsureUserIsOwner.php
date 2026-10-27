<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // التحقق من أن المستخدم Owner أو Admin
        if (!auth()->check() || !auth()->user()->isOwner()) {
            abort(403, 'غير مصرح لك بالوصول إلى لوحة المدير العام');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // تخطي middleware للـ Livewire requests
            if ($this->isLivewireRequest($request)) {
                return $next($request);
            }

            // إذا كان المستخدم مسجل دخول
            if (auth()->check()) {
                $user = auth()->user();
                
                if ($user && $user->tenant_id) {
                    session(['tenant_id' => $user->tenant_id]);
                }
            }
        } catch (\Throwable $e) {
            // تجاهل أي أخطاء في الـ middleware وتسجيلها فقط
            \Log::debug('SetTenantMiddleware error: ' . $e->getMessage());
        }

        return $next($request);
    }

    /**
     * Check if the request is from Livewire
     */
    private function isLivewireRequest(Request $request): bool
    {
        // تحقق من User-Agent
        $userAgent = $request->header('User-Agent', '');
        
        // تحقق من routing
        $path = $request->getPathInfo();
        
        // تخطي livewire routes و ajax requests
        return (
            strpos($path, '/livewire/') === 0 ||
            $request->expectsJson() ||
            strpos($userAgent, 'Livewire') !== false
        );
    }
}

<?php

namespace App\Http\Middleware;

use App\Constants\SystemConstants;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class LoginRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->throttleKey($request);

        // Check if user is locked out
        if (RateLimiter::tooManyAttempts($key, SystemConstants::LOGIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return redirect()->back()
                ->withErrors([
                    'email' => "تم تجاوز الحد الأقصى لمحاولات تسجيل الدخول. يرجى المحاولة بعد {$seconds} ثانية.",
                ])
                ->withInput($request->only('email'));
        }

        $response = $next($request);

        // If login failed, increment attempts
        if ($response->getStatusCode() === 302 && $response->headers->get('Location') === route('filament.admin.auth.login')) {
            RateLimiter::hit($key, SystemConstants::LOGIN_LOCKOUT_MINUTES * 60);
        }

        return $response;
    }

    /**
     * Get throttle key
     */
    protected function throttleKey(Request $request): string
    {
        return 'login:' . strtolower($request->input('email')) . '|' . $request->ip();
    }

    /**
     * Clear login attempts
     */
    public static function clearAttempts(Request $request): void
    {
        $key = 'login:' . strtolower($request->input('email')) . '|' . $request->ip();
        RateLimiter::clear($key);
    }
}

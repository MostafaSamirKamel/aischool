<?php

namespace App\Http\Middleware;

use App\Constants\SystemConstants;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        // Check rate limit
        if (RateLimiter::tooManyAttempts($key, SystemConstants::API_RATE_LIMIT_PER_MINUTE)) {
            return $this->buildRateLimitResponse($key);
        }

        // Increment attempts
        RateLimiter::hit($key, 60);

        $response = $next($request);

        // Add rate limit headers
        return $this->addHeaders(
            $response,
            SystemConstants::API_RATE_LIMIT_PER_MINUTE,
            RateLimiter::retriesLeft($key, SystemConstants::API_RATE_LIMIT_PER_MINUTE)
        );
    }

    /**
     * Resolve request signature
     */
    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return 'api_rate_limit:' . $user->id;
        }

        return 'api_rate_limit:' . $request->ip();
    }

    /**
     * Build rate limit response
     */
    protected function buildRateLimitResponse(string $key): Response
    {
        $retryAfter = RateLimiter::availableIn($key);

        return response()->json([
            'message' => 'تم تجاوز الحد المسموح من الطلبات. يرجى المحاولة بعد ' . $retryAfter . ' ثانية.',
            'retry_after' => $retryAfter,
        ], 429, [
            'X-RateLimit-Retry-After' => $retryAfter,
        ]);
    }

    /**
     * Add rate limit headers
     */
    protected function addHeaders(Response $response, int $maxAttempts, int $remainingAttempts): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remainingAttempts),
        ]);

        return $response;
    }
}

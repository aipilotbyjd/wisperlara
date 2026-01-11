<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByPlan
{
    private array $limits = [
        'free' => 10,
        'pro' => 60,
        'business' => 120,
        'enterprise' => 300,
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $key = 'api:' . $user->id;
        $limit = $this->limits[$user->plan] ?? 10;

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            return response()->json([
                'error' => 'rate_limit_exceeded',
                'message' => 'Too many requests. Please slow down.',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $response = $next($request);

        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($key, $limit));

        return $response;
    }
}

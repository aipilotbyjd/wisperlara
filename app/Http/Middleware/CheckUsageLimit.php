<?php

namespace App\Http\Middleware;

use App\Services\UsageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUsageLimit
{
    public function __construct(
        private UsageService $usageService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$this->usageService->hasAvailableMinutes($user)) {
            return response()->json([
                'error' => 'usage_limit_exceeded',
                'message' => 'Monthly usage limit reached. Please upgrade your plan.',
                'usage' => $this->usageService->getCurrentUsage($user),
            ], 429);
        }

        return $next($request);
    }
}

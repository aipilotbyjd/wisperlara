<?php

namespace App\Http\Middleware;

use App\Services\UsageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUsage
{
    public function __construct(
        private UsageService $usageService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->isSuccessful() && $request->user()) {
            $data = json_decode($response->getContent(), true);
            
            // Track usage based on duration from response
            $duration = $data['duration'] ?? 0;
            if ($duration > 0) {
                $minutes = $duration / 60;
                $this->usageService->trackUsage($request->user(), $minutes);
            }
        }

        return $response;
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\UsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsageController extends Controller
{
    public function __construct(
        private UsageService $usageService
    ) {}

    public function current(Request $request): JsonResponse
    {
        $usage = $this->usageService->getCurrentUsage($request->user());

        return response()->json($usage);
    }

    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'period' => ['nullable', 'string', 'in:month,year'],
        ]);

        $stats = $this->usageService->getUsageStats(
            $request->user(),
            $request->period ?? 'month'
        );

        return response()->json($stats);
    }

    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $history = $this->usageService->getUsageHistory(
            $request->user(),
            $request->days ?? 30
        );

        return response()->json([
            'history' => $history,
        ]);
    }
}

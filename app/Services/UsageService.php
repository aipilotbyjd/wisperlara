<?php

namespace App\Services;

use App\Models\Usage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UsageService
{
    private array $limits = [
        'free' => 30,
        'pro' => 300,
        'business' => -1,
        'enterprise' => -1,
    ];

    public function trackUsage(User $user, float $minutes): void
    {
        Usage::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => Carbon::today(),
            ],
            [
                'minutes_used' => DB::raw("minutes_used + {$minutes}"),
                'transcription_count' => DB::raw('transcription_count + 1'),
            ]
        );
    }

    public function getCurrentUsage(User $user): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $usage = Usage::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->selectRaw('COALESCE(SUM(minutes_used), 0) as total_minutes, COALESCE(SUM(transcription_count), 0) as total_count')
            ->first();

        $limit = $this->limits[$user->plan] ?? 30;

        return [
            'minutes_used' => round($usage->total_minutes ?? 0, 2),
            'minutes_limit' => $limit,
            'transcription_count' => (int) ($usage->total_count ?? 0),
            'days_remaining' => Carbon::now()->diffInDays($endOfMonth),
            'plan' => $user->plan,
            'is_unlimited' => $limit === -1,
            'usage_percentage' => $limit > 0
                ? min(100, round(($usage->total_minutes ?? 0) / $limit * 100, 1))
                : 0,
        ];
    }

    public function hasAvailableMinutes(User $user): bool
    {
        $usage = $this->getCurrentUsage($user);

        if ($usage['is_unlimited']) {
            return true;
        }

        return $usage['minutes_used'] < $usage['minutes_limit'];
    }

    public function getRemainingMinutes(User $user): float
    {
        $usage = $this->getCurrentUsage($user);

        if ($usage['is_unlimited']) {
            return PHP_FLOAT_MAX;
        }

        return max(0, $usage['minutes_limit'] - $usage['minutes_used']);
    }

    public function getUsageStats(User $user, string $period = 'month'): array
    {
        $query = Usage::where('user_id', $user->id);

        if ($period === 'month') {
            $startDate = Carbon::now()->subMonths(12)->startOfMonth();
        } else {
            $startDate = Carbon::now()->subYears(2)->startOfYear();
        }

        $usage = $query->where('date', '>=', $startDate)->get();

        $totalMinutes = $usage->sum('minutes_used');
        $totalTranscriptions = $usage->sum('transcription_count');

        if ($period === 'month') {
            $byPeriod = $usage->groupBy(fn($item) => $item->date->format('Y-m'))
                ->map(fn($group) => [
                    'period' => $group->first()->date->format('Y-m'),
                    'minutes' => round($group->sum('minutes_used'), 2),
                    'transcriptions' => $group->sum('transcription_count'),
                ])->values();
        } else {
            $byPeriod = $usage->groupBy(fn($item) => $item->date->format('Y'))
                ->map(fn($group) => [
                    'period' => $group->first()->date->format('Y'),
                    'minutes' => round($group->sum('minutes_used'), 2),
                    'transcriptions' => $group->sum('transcription_count'),
                ])->values();
        }

        return [
            'total_minutes' => round($totalMinutes, 2),
            'total_transcriptions' => $totalTranscriptions,
            'by_period' => $byPeriod,
        ];
    }

    public function getUsageHistory(User $user, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $usage = Usage::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'desc')
            ->get();

        return $usage->map(fn($item) => [
            'date' => $item->date->format('Y-m-d'),
            'minutes_used' => round($item->minutes_used, 2),
            'transcription_count' => $item->transcription_count,
        ])->toArray();
    }
}

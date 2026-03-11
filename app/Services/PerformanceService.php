<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\PerformanceMetric;
use App\Models\Result;
use App\Models\User;
use Carbon\Carbon;

class PerformanceService
{
    public function calculateDailyScore(User $user, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $date->toDateString();

        $activities = Activity::where('user_id', $user->id)
            ->whereDate('completed_at', $dateStr)
            ->where('is_completed', true)
            ->get();

        $subconsciousPoints = (int) $activities->where('category', 'subconscious')->sum('points');
        $revenuePoints = (int) $activities->whereIn('category', ['task', 'conscious', 'revenue'])->sum('points');

        $cappedSubconscious = min(20, $subconsciousPoints);
        $cappedRevenuePoints = min(145, $revenuePoints);
        $revenueMomentumScore = (int) round(($cappedRevenuePoints / 145) * 100);

        $revenueActionCount = $activities->whereIn('category', ['task', 'conscious', 'revenue'])->count();
        $subconsciousCount = $activities->where('category', 'subconscious')->count();
        $isActiveDay = $revenueActionCount >= 3 && $subconsciousCount >= 2;

        $weekStart = $date->copy()->startOfWeek();
        $weekEnd = $date->copy()->endOfWeek();

        $weekMetrics = PerformanceMetric::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $activeDays = $weekMetrics->filter(function ($metric) {
            $metadata = $metric->metadata ?? [];
            return (bool) ($metadata['is_active_day'] ?? false);
        })->count();

        if ($isActiveDay && !$weekMetrics->contains('date', $dateStr)) {
            $activeDays++;
        }

        $consistencyScore = (int) round(($activeDays / 7) * 100);
        if ($subconsciousCount < 2) {
            $consistencyScore = max(0, $consistencyScore - 10);
        }

        $weeklyPerformanceScore = (int) round(($revenueMomentumScore * 0.6) + ($consistencyScore * 0.4));
        $leaderboardScore = round(($revenueMomentumScore * 0.5) + ($consistencyScore * 0.25) + ($weeklyPerformanceScore * 0.25), 1);

        $leadsFromResults = Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->where('date', $dateStr)
            ->count();

        $dealsFromResults = Result::where('user_id', $user->id)
            ->where('type', 'deal_closed')
            ->where('date', $dateStr)
            ->count();

        $commissionToday = Result::where('user_id', $user->id)
            ->whereIn('type', ['commission', 'deal_closed'])
            ->where('date', $dateStr)
            ->sum('value');

        // Update or Create Metric
        $metric = PerformanceMetric::updateOrCreate(
            ['user_id' => $user->id, 'date' => $dateStr],
            [
                'subconscious_score' => $cappedSubconscious,
                'conscious_score' => $cappedRevenuePoints,
                'results_score' => $consistencyScore,
                'total_momentum_score' => $revenueMomentumScore,
                'leads_generated' => $leadsFromResults,
                'deals_closed' => $dealsFromResults,
                'commission_earned' => $commissionToday,
                'streak_count' => $user->current_streak,
                'metadata' => [
                    'daily_revenue_points' => $cappedRevenuePoints,
                    'raw_revenue_points' => $revenuePoints,
                    'revenue_action_count' => $revenueActionCount,
                    'subconscious_activity_count' => $subconsciousCount,
                    'revenue_momentum_score' => $revenueMomentumScore,
                    'consistency_index' => $consistencyScore,
                    'weekly_performance_score' => $weeklyPerformanceScore,
                    'leaderboard_score' => $leaderboardScore,
                    'is_active_day' => $isActiveDay,
                ],
            ]
        );

        return $metric;
    }

    public function updateStreak(User $user)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $subCount = Activity::where('user_id', $user->id)
            ->whereDate('completed_at', $today->toDateString())
            ->where('is_completed', true)
            ->where('category', 'subconscious')
            ->count();

        $revenueCount = Activity::where('user_id', $user->id)
            ->whereDate('completed_at', $today->toDateString())
            ->where('is_completed', true)
            ->whereIn('category', ['task', 'conscious', 'revenue'])
            ->count();

        if ($subCount >= 2 && $revenueCount >= 3) {
            if ($user->last_activity_date != $today->toDateString()) {
                if ($user->last_activity_date == $yesterday->toDateString()) {
                    $user->current_streak += 1;
                } else {
                    $user->current_streak = 1;
                }
                $user->longest_streak = max($user->longest_streak ?? 0, $user->current_streak);
                $user->last_activity_date = $today->toDateString();
                $user->save();
            }
        }

        return $user->current_streak;
    }

    public function getActivityPoints($type)
    {
        $normalizedType = strtolower((string) $type);

        $activityType = \App\Models\ActivityType::where('type_key', $normalizedType)
            ->orWhere('name', $type)
            ->first();

        if ($activityType) {
            return $activityType->points;
        }

        $points = [
            'cold_calling_block' => 6,
            'follow_up_block' => 8,
            'client_meeting' => 12,
            'site_visit' => 15,
            'content_creation' => 4,
            'content_posting' => 3,
            'prospecting_session' => 7,
            'deal_negotiation' => 18,
            'crm_update' => 2,
            'referral_ask' => 6,
            'deal_closed' => 40,
            'network_event' => 10,
            'proposal_sent' => 14,
            'visualization' => 8,
            'affirmations' => 6,
            'gratitude_journaling' => 6,
            'mindset_training' => 8,
            'audio_reprogramming' => 6,
            'webinar_attendance' => 10,
            'belief_exercise' => 8,
            'calm_reset' => 5,
            'identity_statement' => 5,
            'morning_focus_ritual' => 6,
        ];

        return $points[$normalizedType] ?? 0;
    }
}

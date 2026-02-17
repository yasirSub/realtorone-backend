<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\LeaderboardCache;
use App\Models\PerformanceMetric;
use App\Models\Result;
use App\Models\User;
use App\Models\WeeklyScore;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    /**
     * Refresh all leaderboard categories for today
     */
    public function refreshLeaderboards()
    {
        $today = Carbon::today()->toDateString();
        $weekStart = Carbon::today()->startOfWeek()->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateString();

        $this->buildConsistencyLeaderboard($today, $weekStart);
        $this->buildMomentumClimbersLeaderboard($today);
        $this->buildDealMakersLeaderboard($today, $monthStart);
        $this->buildRevenueLeaderboard($today, $monthStart);
        $this->buildIdentityDisciplineLeaderboard($today, $weekStart);
    }

    /**
     * Category 1: Consistency Leaders - who logs in every day
     */
    private function buildConsistencyLeaderboard($today, $weekStart)
    {
        $users = User::whereNotNull('last_activity_date')->get();

        $scores = $users->map(function ($user) use ($weekStart) {
            $daysActive = PerformanceMetric::where('user_id', $user->id)
                ->where('date', '>=', $weekStart)
                ->where('total_momentum_score', '>', 0)
                ->count();

            return [
                'user_id' => $user->id,
                'score' => round(($daysActive / 7) * 100),
                'metadata' => [
                    'days_active' => $daysActive,
                    'streak' => $user->current_streak,
                ],
            ];
        })->sortByDesc('score')->values();

        $this->saveLeaderboard($scores, 'consistency', 'weekly', $today);
    }

    /**
     * Category 2: Momentum Climbers - biggest score improvement week over week
     */
    private function buildMomentumClimbersLeaderboard($today)
    {
        $users = User::whereNotNull('last_activity_date')->get();
        $thisWeekStart = Carbon::today()->startOfWeek();
        $lastWeekStart = $thisWeekStart->copy()->subWeek();

        $scores = $users->map(function ($user) use ($thisWeekStart, $lastWeekStart) {
            $thisWeekAvg = PerformanceMetric::where('user_id', $user->id)
                ->where('date', '>=', $thisWeekStart->toDateString())
                ->avg('total_momentum_score') ?? 0;

            $lastWeekAvg = PerformanceMetric::where('user_id', $user->id)
                ->where('date', '>=', $lastWeekStart->toDateString())
                ->where('date', '<', $thisWeekStart->toDateString())
                ->avg('total_momentum_score') ?? 0;

            $improvement = $lastWeekAvg > 0
                ? round((($thisWeekAvg - $lastWeekAvg) / $lastWeekAvg) * 100)
                : ($thisWeekAvg > 0 ? 100 : 0);

            return [
                'user_id' => $user->id,
                'score' => max(0, $improvement), // Never show negative
                'metadata' => [
                    'this_week_avg' => round($thisWeekAvg),
                    'last_week_avg' => round($lastWeekAvg),
                ],
            ];
        })->sortByDesc('score')->values();

        $this->saveLeaderboard($scores, 'momentum_climber', 'weekly', $today);
    }

    /**
     * Category 3: Deal Makers - most closed deals this month
     */
    private function buildDealMakersLeaderboard($today, $monthStart)
    {
        $results = Result::where('type', 'deal_closed')
            ->where('date', '>=', $monthStart)
            ->select('user_id', DB::raw('COUNT(*) as deal_count'), DB::raw('SUM(value) as total_value'))
            ->groupBy('user_id')
            ->orderByDesc('deal_count')
            ->get();

        $scores = $results->map(function ($r) {
            return [
                'user_id' => $r->user_id,
                'score' => $r->deal_count * 10, // 10 points per deal
                'metadata' => [
                    'deals_closed' => $r->deal_count,
                    'total_value' => $r->total_value,
                ],
            ];
        })->values();

        $this->saveLeaderboard($scores, 'deal_maker', 'monthly', $today);
    }

    /**
     * Category 4: Revenue Leaders - highest commission this month
     */
    private function buildRevenueLeaderboard($today, $monthStart)
    {
        $results = Result::where('type', 'commission')
            ->where('date', '>=', $monthStart)
            ->select('user_id', DB::raw('SUM(value) as total_commission'))
            ->groupBy('user_id')
            ->orderByDesc('total_commission')
            ->get();

        $scores = $results->map(function ($r) {
            return [
                'user_id' => $r->user_id,
                'score' => (int) $r->total_commission,
                'metadata' => [
                    'total_commission_aed' => $r->total_commission,
                ],
            ];
        })->values();

        $this->saveLeaderboard($scores, 'revenue', 'monthly', $today);
    }

    /**
     * Category 5: Identity Discipline - highest subconscious score average
     */
    private function buildIdentityDisciplineLeaderboard($today, $weekStart)
    {
        $metrics = PerformanceMetric::where('date', '>=', $weekStart)
            ->select('user_id', DB::raw('AVG(subconscious_score) as avg_sub'))
            ->groupBy('user_id')
            ->orderByDesc('avg_sub')
            ->get();

        $scores = $metrics->map(function ($m) {
            return [
                'user_id' => $m->user_id,
                'score' => round($m->avg_sub),
                'metadata' => [
                    'avg_subconscious' => round($m->avg_sub, 1),
                ],
            ];
        })->values();

        $this->saveLeaderboard($scores, 'identity_discipline', 'weekly', $today);
    }

    /**
     * Save a leaderboard with rankings
     */
    private function saveLeaderboard($scores, $category, $period, $today)
    {
        $rank = 1;
        foreach ($scores as $entry) {
            LeaderboardCache::updateOrCreate(
                [
                    'user_id' => $entry['user_id'],
                    'category' => $category,
                    'period' => $period,
                    'period_date' => $today,
                ],
                [
                    'score' => $entry['score'],
                    'rank' => $rank,
                    'metadata' => $entry['metadata'] ?? null,
                ]
            );
            $rank++;
        }
    }

    /**
     * Get leaderboard for a category — with protective psychology
     * Never shows user in bottom. Shows micro-groups.
     */
    public function getLeaderboard($category, $period = 'weekly', $userId = null, $limit = 20)
    {
        $today = Carbon::today()->toDateString();

        $entries = LeaderboardCache::with('user:id,name,profile_photo_path,current_streak,rank')
            ->where('category', $category)
            ->where('period', $period)
            ->where('period_date', $today)
            ->orderBy('rank')
            ->limit($limit)
            ->get();

        // If user not in top, find their position and show surrounding users
        $userEntry = null;
        if ($userId) {
            $userEntry = LeaderboardCache::where('category', $category)
                ->where('period', $period)
                ->where('period_date', $today)
                ->where('user_id', $userId)
                ->first();
        }

        return [
            'leaderboard' => $entries->map(function ($e) {
                return [
                    'rank' => $e->rank,
                    'user_id' => $e->user_id,
                    'user_name' => $e->user->name ?? 'Agent',
                    'photo' => $e->user->profile_photo_path ? url('storage/' . $e->user->profile_photo_path) : null,
                    'score' => $e->score,
                    'streak' => $e->user->current_streak ?? 0,
                    'metadata' => $e->metadata,
                ];
            }),
            'my_position' => $userEntry ? [
                'rank' => $userEntry->rank,
                'score' => $userEntry->score,
                'metadata' => $userEntry->metadata,
                // Protective psychology: show positive messaging
                'message' => $this->getMotivationalMessage($userEntry->rank, $category),
            ] : null,
            'total_participants' => LeaderboardCache::where('category', $category)
                ->where('period', $period)
                ->where('period_date', $today)
                ->count(),
        ];
    }

    /**
     * Protective Psychology - Never demotivate the user
     */
    private function getMotivationalMessage($rank, $category)
    {
        if ($rank <= 3) {
            return "🔥 You're in the Top 3! Supreme performance.";
        } elseif ($rank <= 10) {
            return "⚡ Top 10! You're outperforming most agents. Keep pushing.";
        } elseif ($rank <= 25) {
            return "📈 Climbing the ranks! Consistency will get you to the top.";
        } else {
            // NEVER say "you're ranked #47" — always positive
            $messages = [
                'consistency' => "💪 Every day you show up, you're ahead of most. Keep building momentum.",
                'momentum_climber' => "🚀 Your trajectory matters more than your position. Progress is everything.",
                'deal_maker' => "🎯 Focus on pipeline quality. One great deal can change everything.",
                'revenue' => "💎 Revenue follows consistency. You're building the foundation.",
                'identity_discipline' => "🧠 Identity work is invisible progress. Trust the process.",
            ];
            return $messages[$category] ?? "📊 You're making progress. Keep going.";
        }
    }
}

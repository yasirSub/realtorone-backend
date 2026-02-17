<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Models\PerformanceMetric;
use App\Models\Result;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BadgeService
{
    /**
     * Check and award badges to a user after an activity
     */
    public function checkAndAwardBadges(User $user)
    {
        $awarded = [];

        // Ensure default badges exist
        $this->seedDefaultBadges();

        // Check each badge type
        $awarded = array_merge($awarded, $this->checkStreakBadges($user));
        $awarded = array_merge($awarded, $this->checkScoreBadges($user));
        $awarded = array_merge($awarded, $this->checkDealBadges($user));
        $awarded = array_merge($awarded, $this->checkConsistencyBadges($user));
        $awarded = array_merge($awarded, $this->checkMilestoneBadges($user));

        return $awarded;
    }

    private function checkStreakBadges(User $user)
    {
        $awarded = [];
        $streak = $user->current_streak;

        $streakBadges = [
            3 => 'streak_3',
            7 => 'streak_7',
            14 => 'streak_14',
            30 => 'streak_30',
            60 => 'streak_60',
            100 => 'streak_100',
        ];

        foreach ($streakBadges as $threshold => $slug) {
            if ($streak >= $threshold) {
                $badge = $this->awardBadge($user, $slug);
                if ($badge) $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    private function checkScoreBadges(User $user)
    {
        $awarded = [];
        $today = Carbon::today()->toDateString();

        $metric = PerformanceMetric::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$metric) return $awarded;

        // Perfect Day: score 90+
        if ($metric->total_momentum_score >= 90) {
            $badge = $this->awardBadge($user, 'perfect_day');
            if ($badge) $awarded[] = $badge;
        }

        // Momentum Builder: score 70+ for 5 consecutive days
        $last5 = PerformanceMetric::where('user_id', $user->id)
            ->where('date', '>=', Carbon::today()->subDays(4)->toDateString())
            ->where('total_momentum_score', '>=', 70)
            ->count();

        if ($last5 >= 5) {
            $badge = $this->awardBadge($user, 'momentum_builder');
            if ($badge) $awarded[] = $badge;
        }

        // Identity Master: subconscious_score = 40 (max)
        if ($metric->subconscious_score >= 40) {
            $badge = $this->awardBadge($user, 'identity_master');
            if ($badge) $awarded[] = $badge;
        }

        return $awarded;
    }

    private function checkDealBadges(User $user)
    {
        $awarded = [];
        $monthStart = Carbon::today()->startOfMonth()->toDateString();

        $dealsThisMonth = Result::where('user_id', $user->id)
            ->where('type', 'deal_closed')
            ->where('date', '>=', $monthStart)
            ->count();

        $dealBadges = [
            1 => 'first_deal',
            3 => 'deal_hat_trick',
            5 => 'deal_machine',
            10 => 'deal_legend',
        ];

        foreach ($dealBadges as $threshold => $slug) {
            if ($dealsThisMonth >= $threshold) {
                $badge = $this->awardBadge($user, $slug);
                if ($badge) $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    private function checkConsistencyBadges(User $user)
    {
        $awarded = [];
        $weekStart = Carbon::today()->startOfWeek()->toDateString();

        $daysActive = PerformanceMetric::where('user_id', $user->id)
            ->where('date', '>=', $weekStart)
            ->where('total_momentum_score', '>', 0)
            ->count();

        // Full Week: 7/7 days
        if ($daysActive >= 7) {
            $badge = $this->awardBadge($user, 'full_week');
            if ($badge) $awarded[] = $badge;
        }

        // 5-Day Warrior: 5/7 days
        if ($daysActive >= 5) {
            $badge = $this->awardBadge($user, 'five_day_warrior');
            if ($badge) $awarded[] = $badge;
        }

        return $awarded;
    }

    private function checkMilestoneBadges(User $user)
    {
        $awarded = [];

        // Total activities milestones
        $totalActivities = DB::table('activities')
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        $milestones = [
            50 => 'activity_50',
            100 => 'activity_100',
            500 => 'activity_500',
            1000 => 'activity_1000',
        ];

        foreach ($milestones as $threshold => $slug) {
            if ($totalActivities >= $threshold) {
                $badge = $this->awardBadge($user, $slug);
                if ($badge) $awarded[] = $badge;
            }
        }

        return $awarded;
    }

    /**
     * Award a badge to user if not already earned today
     */
    private function awardBadge(User $user, $slug)
    {
        $badge = Badge::where('slug', $slug)->first();
        if (!$badge) return null;

        $today = Carbon::today()->toDateString();

        // Check if already earned (daily badges can be re-earned, milestones only once)
        $query = DB::table('user_badges')
            ->where('user_id', $user->id)
            ->where('badge_id', $badge->id);

        if ($badge->type === 'daily') {
            $query->where('earned_at', $today);
        }

        if ($query->exists()) return null;

        DB::table('user_badges')->insert([
            'user_id' => $user->id,
            'badge_id' => $badge->id,
            'earned_at' => $today,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'badge_id' => $badge->id,
            'name' => $badge->name,
            'icon' => $badge->icon,
            'color' => $badge->color,
            'description' => $badge->description,
            'rarity' => $badge->rarity,
        ];
    }

    /**
     * Get all badges for a user
     */
    public function getUserBadges($userId)
    {
        $allBadges = Badge::all();
        $earnedBadgeIds = DB::table('user_badges')
            ->where('user_id', $userId)
            ->pluck('badge_id')
            ->unique()
            ->toArray();

        return $allBadges->map(function ($badge) use ($earnedBadgeIds) {
            return [
                'id' => $badge->id,
                'name' => $badge->name,
                'slug' => $badge->slug,
                'description' => $badge->description,
                'icon' => $badge->icon,
                'color' => $badge->color,
                'type' => $badge->type,
                'rarity' => $badge->rarity,
                'earned' => in_array($badge->id, $earnedBadgeIds),
            ];
        });
    }

    /**
     * Seed default badges if they don't exist
     */
    public function seedDefaultBadges()
    {
        $badges = [
            // Streak badges
            ['name' => 'Rising Flame', 'slug' => 'streak_3', 'description' => '3-day streak! Momentum is building.', 'icon' => '🔥', 'color' => '#FF6B35', 'type' => 'milestone', 'rarity' => 1],
            ['name' => 'Week Warrior', 'slug' => 'streak_7', 'description' => '7-day streak! A full week of consistency.', 'icon' => '⚔️', 'color' => '#FF4500', 'type' => 'milestone', 'rarity' => 2],
            ['name' => 'Fortnight Force', 'slug' => 'streak_14', 'description' => '14-day streak! Two weeks of unstoppable momentum.', 'icon' => '💎', 'color' => '#9B59B6', 'type' => 'milestone', 'rarity' => 2],
            ['name' => 'Monthly Machine', 'slug' => 'streak_30', 'description' => '30-day streak! You are a consistency powerhouse.', 'icon' => '🏅', 'color' => '#F1C40F', 'type' => 'milestone', 'rarity' => 3],
            ['name' => 'Iron Will', 'slug' => 'streak_60', 'description' => '60-day streak! Your discipline is legendary.', 'icon' => '🛡️', 'color' => '#2ECC71', 'type' => 'milestone', 'rarity' => 3],
            ['name' => 'Century Legend', 'slug' => 'streak_100', 'description' => '100-day streak! Welcome to the 1% Club.', 'icon' => '👑', 'color' => '#E74C3C', 'type' => 'milestone', 'rarity' => 4],

            // Score badges
            ['name' => 'Perfect Day', 'slug' => 'perfect_day', 'description' => 'Scored 90+ in a single day. Elite performance.', 'icon' => '💯', 'color' => '#00D4AA', 'type' => 'daily', 'rarity' => 3],
            ['name' => 'Momentum Builder', 'slug' => 'momentum_builder', 'description' => '70+ score for 5 consecutive days. Unstoppable.', 'icon' => '🚀', 'color' => '#3498DB', 'type' => 'weekly', 'rarity' => 3],
            ['name' => 'Identity Master', 'slug' => 'identity_master', 'description' => 'Maxed out identity conditioning in one day.', 'icon' => '🧠', 'color' => '#8E44AD', 'type' => 'daily', 'rarity' => 2],

            // Deal badges
            ['name' => 'First Blood', 'slug' => 'first_deal', 'description' => 'Logged your first closed deal. The journey begins!', 'icon' => '🎯', 'color' => '#E67E22', 'type' => 'milestone', 'rarity' => 1],
            ['name' => 'Hat Trick', 'slug' => 'deal_hat_trick', 'description' => '3 deals in a month. You\'re in rhythm.', 'icon' => '🎩', 'color' => '#1ABC9C', 'type' => 'monthly', 'rarity' => 2],
            ['name' => 'Deal Machine', 'slug' => 'deal_machine', 'description' => '5 deals in a month. The market knows your name.', 'icon' => '⚡', 'color' => '#F39C12', 'type' => 'monthly', 'rarity' => 3],
            ['name' => 'Deal Legend', 'slug' => 'deal_legend', 'description' => '10 deals in a month. Absolute domination.', 'icon' => '🏆', 'color' => '#FFD700', 'type' => 'monthly', 'rarity' => 4],

            // Consistency badges
            ['name' => 'Full Week', 'slug' => 'full_week', 'description' => 'Active all 7 days this week. Maximum consistency.', 'icon' => '📅', 'color' => '#2980B9', 'type' => 'weekly', 'rarity' => 2],
            ['name' => '5-Day Warrior', 'slug' => 'five_day_warrior', 'description' => '5+ active days this week. Strong commitment.', 'icon' => '💪', 'color' => '#27AE60', 'type' => 'weekly', 'rarity' => 1],

            // Activity milestones
            ['name' => '50 Actions', 'slug' => 'activity_50', 'description' => '50 completed activities. Building the habit.', 'icon' => '📊', 'color' => '#7F8C8D', 'type' => 'milestone', 'rarity' => 1],
            ['name' => '100 Actions', 'slug' => 'activity_100', 'description' => '100 completed activities. Habits are forming.', 'icon' => '📈', 'color' => '#2C3E50', 'type' => 'milestone', 'rarity' => 2],
            ['name' => '500 Actions', 'slug' => 'activity_500', 'description' => '500 completed activities. A true professional.', 'icon' => '🌟', 'color' => '#E74C3C', 'type' => 'milestone', 'rarity' => 3],
            ['name' => '1000 Actions', 'slug' => 'activity_1000', 'description' => '1000 completed activities. You are the system.', 'icon' => '🔱', 'color' => '#9B59B6', 'type' => 'milestone', 'rarity' => 4],
        ];

        foreach ($badges as $badgeData) {
            Badge::firstOrCreate(
                ['slug' => $badgeData['slug']],
                $badgeData
            );
        }
    }
}

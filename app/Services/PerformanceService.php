<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\PerformanceMetric;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

        $subconsciousPoints = $activities->where('category', 'subconscious')->sum('points');
        $consciousPoints = $activities->whereIn('category', ['task', 'conscious'])->sum('points');
        
        // Cap Subconscious and Conscious as per requirements
        $cappedSubconscious = min(40, $subconsciousPoints);
        $cappedConscious = min(45, $consciousPoints);

        // Results logic — pull from both activities and results table
        $leadsFromActivities = $activities->where('type', 'leadOutreach')->sum('quantity');
        $dealsFromActivities = $activities->where('type', 'meeting')->sum('quantity');
        
        // Also check the dedicated results table
        $leadsFromResults = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->where('date', $dateStr)
            ->count();

        $dealsFromResults = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'deal_closed')
            ->where('date', $dateStr)
            ->count();

        $commissionToday = \App\Models\Result::where('user_id', $user->id)
            ->whereIn('type', ['commission', 'deal_closed'])
            ->where('date', $dateStr)
            ->sum('value');

        $totalLeads = $leadsFromActivities + $leadsFromResults;
        $totalDeals = $dealsFromActivities + $dealsFromResults;
        
        // Points: leads = +3 each, deals = +10 each, commission logged = +2
        $resultsPoints = ($totalLeads * 3) + ($totalDeals * 10) + ($commissionToday > 0 ? 2 : 0);
        $cappedResults = min(15, $resultsPoints);

        $totalScore = $cappedSubconscious + $cappedConscious + $cappedResults;
        $totalScore = min(100, $totalScore);

        // Update or Create Metric
        $metric = PerformanceMetric::updateOrCreate(
            ['user_id' => $user->id, 'date' => $dateStr],
            [
                'subconscious_score' => $cappedSubconscious,
                'conscious_score' => $cappedConscious,
                'results_score' => $cappedResults,
                'total_momentum_score' => $totalScore,
                'leads_generated' => $totalLeads,
                'deals_closed' => $totalDeals,
                'commission_earned' => $commissionToday,
                'streak_count' => $user->current_streak
            ]
        );

        return $metric;
    }

    public function updateStreak(User $user)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayMetric = PerformanceMetric::where('user_id', $user->id)
            ->where('date', $today->toDateString())
            ->first();

        // Minimum requirement for streak: 
        // Subconscious >= 14 (approx 2 activities of ~7 points)
        // Conscious >= 24 (approx 4 activities of ~6 points)
        // Actually the doc says "Minimum Mandatory: 2 Subconscious, 4 Conscious"
        
        $subCount = Activity::where('user_id', $user->id)
            ->whereDate('completed_at', $today->toDateString())
            ->where('is_completed', true)
            ->where('category', 'subconscious')
            ->count();

        $conCount = Activity::where('user_id', $user->id)
            ->whereDate('completed_at', $today->toDateString())
            ->where('is_completed', true)
            ->whereIn('category', ['task', 'conscious'])
            ->count();

        if ($subCount >= 2 && $conCount >= 4) {
            if ($user->last_activity_date != $today->toDateString()) {
                if ($user->last_activity_date == $yesterday->toDateString()) {
                    $user->current_streak += 1;
                } else {
                    $user->current_streak = 1;
                }
                $user->last_activity_date = $today->toDateString();
                $user->save();
            }
        }

        return $user->current_streak;
    }

    public function getActivityPoints($type)
    {
        // Try to find in DB first
        $activityType = \App\Models\ActivityType::where('type_key', $type)
            ->orWhere('name', $type)
            ->first();

        if ($activityType) {
            return $activityType->points;
        }

        // Fallback for legacy/hardcoded types if not in DB
        $points = [
            // Identity Conditioning (Min 2, Max 40)
            'journaling' => 4,
            'webinar' => 12,
            'visualization' => 10,
            'affirmations' => 8,
            'inner_game_audio' => 8,
            'guided_reset' => 6,
            
            // Conscious
            'coldCalling' => 8,
            'contentCreation' => 8,
            'contentPosting' => 6,
            'dmConversation' => 6,
            'whatsappBroadcast' => 6,
            'email' => 6,
            'meeting' => 10,
            'prospecting' => 8,
            'followUp' => 8,
            'negotiation' => 10,
            'servicing' => 6,
            'crmUpdate' => 5,
            'siteVisit' => 10,
            'referralAsk' => 6,
            'skillTraining' => 8,
        ];

        return $points[$type] ?? 0;
    }
}

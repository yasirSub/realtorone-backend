<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@realtorone.com'],
            [
                'name' => 'Root Admin',
                'password' => Hash::make('password123'),
                'membership_tier' => 'Titan'
            ]
        );

        // Gold Operator
        $goldUser = User::updateOrCreate(
            ['email' => 'myname@gmail.com'],
            [
                'name' => 'Elite Practitioner',
                'password' => Hash::make('password123'),
                'growth_score' => 94,
                'execution_rate' => 91,
                'mindset_index' => 9,
                'current_streak' => 18,
                'is_premium' => true,
                'membership_tier' => 'Titan',
                'diagnosis_scores' => ['branding' => 95, 'lead_gen' => 88, 'sales' => 92, 'mindset' => 94]
            ]
        );

        // Silver Operator
        $silverUser = User::updateOrCreate(
            ['email' => 'realtorone@example.com'],
            [
                'name' => 'Growth Operator',
                'password' => Hash::make('password123'),
                'growth_score' => 76,
                'execution_rate' => 68,
                'mindset_index' => 7,
                'current_streak' => 8,
                'is_premium' => true,
                'membership_tier' => 'Rainmaker',
                'diagnosis_scores' => ['branding' => 70, 'lead_gen' => 75, 'sales' => 65, 'mindset' => 80]
            ]
        );

        // Free Operator
        User::updateOrCreate(
            ['email' => 'realtortwo@example.com'],
            [
                'name' => 'New Practitioner',
                'password' => Hash::make('password123'),
                'growth_score' => 32,
                'execution_rate' => 25,
                'mindset_index' => 4,
                'current_streak' => 2,
                'is_premium' => false,
                'membership_tier' => 'Consultant',
                'diagnosis_scores' => ['branding' => 30, 'lead_gen' => 20, 'sales' => 15, 'mindset' => 40]
            ]
        );

        // Diamond Operator
        $diamondUser = User::updateOrCreate(
            ['email' => 'diamond@example.com'],
            [
                'name' => 'Iconic Leader',
                'password' => Hash::make('password123'),
                'growth_score' => 99,
                'execution_rate' => 98,
                'mindset_index' => 10,
                'current_streak' => 365,
                'is_premium' => true,
                'membership_tier' => 'Titan',
                'diagnosis_scores' => ['branding' => 99, 'lead_gen' => 98, 'sales' => 97, 'mindset' => 100]
            ]
        );

        \App\Models\SubscriptionPackage::updateOrCreate(
            ['name' => 'Consultant'],
            ['tier_level' => 0, 'price_monthly' => 0.00, 'description' => 'Standard behavioral tracking.', 'features' => ['Activity Log']]
        );

        \App\Models\SubscriptionPackage::updateOrCreate(
            ['name' => 'Rainmaker'],
            ['tier_level' => 1, 'price_monthly' => 210.00, 'description' => 'Advanced execution tools and deeper analytics.', 'features' => ['Market Analytics', 'Priority Support']]
        );

        $titanGold = \App\Models\SubscriptionPackage::updateOrCreate(
            ['name' => 'Titan'],
            ['tier_level' => 2, 'price_monthly' => 420.00, 'description' => 'Maximum performance infrastructure for elite operators. Includes all premium features.', 'features' => ['Pro Mastermind Access', 'Dedicated Account Manager', 'Inner Circle Access', 'Personal Coaching', 'Unlimited Resources']]
        );

        \App\Models\Coupon::updateOrCreate(
            ['code' => 'SIR20'],
            ['discount_percentage' => 20, 'max_uses' => 50]
        );

        // Assign Titan to myname@gmail.com
        \App\Models\UserSubscription::updateOrCreate(
            ['user_id' => $goldUser->id],
            [
                'package_id' => $titanGold->id,
                'started_at' => now(),
                'expires_at' => now()->addMonths(12),
                'status' => 'active',
                'payment_method' => 'stripe',
                'payment_id' => 'SEED_TITAN_GOLD_' . time(),
                'amount_paid' => 420.00,
            ]
        );

        // Assign Rainmaker to realtorone
        $rainmaker = \App\Models\SubscriptionPackage::where('name', 'Rainmaker')->first();
        \App\Models\UserSubscription::updateOrCreate(
            ['user_id' => $silverUser->id],
            [
                'package_id' => $rainmaker->id,
                'started_at' => now(),
                'expires_at' => now()->addMonths(1),
                'status' => 'active',
                'payment_method' => 'paypal',
                'payment_id' => 'SEED_RAINMAKER_' . time(),
                'amount_paid' => 210.00,
            ]
        );

        // Assign Titan to diamond user (merged Diamond into Titan)
        \App\Models\UserSubscription::updateOrCreate(
            ['user_id' => $diamondUser->id],
            [
                'package_id' => $titanGold->id,
                'started_at' => now(),
                'expires_at' => now()->addMonths(12),
                'status' => 'active',
                'payment_method' => 'stripe',
                'payment_id' => 'SEED_TITAN_GOLD_' . time(),
                'amount_paid' => 420.00,
            ]
        );

        // Seed Courses - Module Structure
        // Module 1: Invisible Influence Belief + Identity (All tiers)
        $module1Courses = [
            ['title' => 'Identity Declaration', 'description' => 'Define your professional identity and core values.', 'min_tier' => 'Consultant', 'module_number' => 1, 'sequence' => 1, 'url' => 'https://example.com/module1-identity'],
            ['title' => 'Revenue Activation', 'description' => 'Activate your revenue-generating mindset and beliefs.', 'min_tier' => 'Consultant', 'module_number' => 1, 'sequence' => 2, 'url' => 'https://example.com/module1-revenue'],
            ['title' => 'Belief System Foundation', 'description' => 'Build the foundation of your success beliefs.', 'min_tier' => 'Consultant', 'module_number' => 1, 'sequence' => 3, 'url' => 'https://example.com/module1-beliefs'],
        ];

        // Module 2: Million Dirham Beliefs (Rainmaker+)
        $module2Courses = [
            ['title' => 'Million Dirham Mindset', 'description' => 'Upgrade to unlock: Develop beliefs that attract high-value opportunities.', 'min_tier' => 'Rainmaker', 'module_number' => 2, 'sequence' => 1, 'url' => 'https://example.com/module2-mindset'],
            ['title' => 'Wealth Consciousness', 'description' => 'Upgrade to unlock: Shift your consciousness to attract abundance.', 'min_tier' => 'Rainmaker', 'module_number' => 2, 'sequence' => 2, 'url' => 'https://example.com/module2-wealth'],
            ['title' => 'Elite Operator Identity', 'description' => 'Upgrade to unlock: Embody the identity of a top-performing agent.', 'min_tier' => 'Rainmaker', 'module_number' => 2, 'sequence' => 3, 'url' => 'https://example.com/module2-identity'],
        ];

        // Module 3: Cold Calling System Elite Execution (Titan only)
        $module3Courses = [
            ['title' => 'Cold Calling System', 'description' => 'Elite execution: Master the art of high-converting cold calls.', 'min_tier' => 'Titan', 'module_number' => 3, 'sequence' => 1, 'url' => 'https://example.com/module3-coldcalling'],
            ['title' => 'Objection Handling Mastery', 'description' => 'Elite execution: Turn objections into opportunities.', 'min_tier' => 'Titan', 'module_number' => 3, 'sequence' => 2, 'url' => 'https://example.com/module3-objections'],
            ['title' => 'Closing Techniques', 'description' => 'Elite execution: Advanced closing strategies for high-value deals.', 'min_tier' => 'Titan', 'module_number' => 3, 'sequence' => 3, 'url' => 'https://example.com/module3-closing'],
        ];

        $allCourses = array_merge($module1Courses, $module2Courses, $module3Courses);

        foreach ($allCourses as $course) {
            \App\Models\Course::updateOrCreate(['title' => $course['title']], $course);
        }

        // Seed Activity Types from Master Document
        $activityTypes = [
            // --- SUBCONSCIOUS ---
            ['name' => 'Visualization', 'points' => 8, 'category' => 'subconscious', 'type_key' => 'visualization', 'icon' => 'Eye', 'min_tier' => 'Consultant'],
            ['name' => 'Affirmations', 'points' => 6, 'category' => 'subconscious', 'type_key' => 'affirmations', 'icon' => 'Repeat', 'min_tier' => 'Consultant'],
            ['name' => 'Audio Reprogramming', 'points' => 6, 'category' => 'subconscious', 'type_key' => 'audio_reprogramming', 'icon' => 'Headphones', 'min_tier' => 'Rainmaker'],
            ['name' => 'Belief Exercise', 'points' => 8, 'category' => 'subconscious', 'type_key' => 'belief_exercise', 'icon' => 'BookOpen', 'min_tier' => 'Titan'],
            ['name' => 'Identity Statement', 'points' => 5, 'category' => 'subconscious', 'type_key' => 'identity_statement', 'icon' => 'Shield', 'min_tier' => 'Titan'],
            
            // --- CONSCIOUS ---
            ['name' => 'Cold Calling', 'points' => 8, 'category' => 'conscious', 'type_key' => 'cold_calling', 'icon' => 'Phone', 'min_tier' => 'Consultant'],
            ['name' => 'Content Creation', 'points' => 8, 'category' => 'conscious', 'type_key' => 'content_creation', 'icon' => 'Video', 'min_tier' => 'Consultant'],
            ['name' => 'DM Conversations', 'points' => 6, 'category' => 'conscious', 'type_key' => 'dm_convos', 'icon' => 'MessageSquare', 'min_tier' => 'Rainmaker'],
            ['name' => 'Client Meetings', 'points' => 10, 'category' => 'conscious', 'type_key' => 'client_meetings', 'icon' => 'Users', 'min_tier' => 'Rainmaker'],
            ['name' => 'Deal Negotiation', 'points' => 10, 'category' => 'conscious', 'type_key' => 'negotiation', 'icon' => 'Gavel', 'min_tier' => 'Titan'],
            ['name' => 'CRM Update', 'points' => 5, 'category' => 'conscious', 'type_key' => 'crm_update', 'icon' => 'Database', 'min_tier' => 'Consultant'],
            ['name' => 'Site Visits', 'points' => 10, 'category' => 'conscious', 'type_key' => 'site_visits', 'icon' => 'MapPin', 'min_tier' => 'Titan'],
            ['name' => 'Luxury Outreach', 'points' => 15, 'category' => 'conscious', 'type_key' => 'luxury_outreach', 'icon' => 'Star', 'min_tier' => 'Titan'],
        ];

        foreach ($activityTypes as $at) {
            \App\Models\ActivityType::updateOrCreate(['type_key' => $at['type_key']], array_merge($at, ['is_global' => true]));
        }

        // Generate 10 additional users with history
        $tiers = ['Rainmaker', 'Titan', 'Titan', 'Consultant'];
        $names = ['James Rodriguez', 'Sarah Chen', 'Michael Olayinka', 'Elena Petrova', 'David Wilson', 'Aria Gupta', 'Liam O\'Shea', 'Sophia Kim', 'Lucas Silva', 'Emma Watson'];
        
        foreach ($names as $index => $name) {
            $tier = $tiers[$index % 4];
            // Remove "GOLD" from tier names
            $tier = str_replace([' - GOLD', '- GOLD', ' GOLD', 'GOLD'], '', $tier);
            if (empty($tier) || $tier === 'Titan') {
                $tier = 'Titan';
            }
            $email = strtolower(str_replace(' ', '.', $name)) . '@example.com';
            
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password123'),
                    'membership_tier' => $tier,
                    'is_premium' => $tier !== 'Consultant',
                    'growth_score' => rand(30, 95),
                    'execution_rate' => rand(40, 98),
                    'mindset_index' => rand(5, 10),
                    'current_streak' => rand(1, 30),
                    'phone_number' => '+1' . rand(100, 999) . rand(100, 999) . rand(1000, 9999),
                    'license_number' => 'RE' . rand(10000, 99999),
                    'diagnosis_scores' => [
                        'branding' => rand(30, 100),
                        'lead_gen' => rand(30, 100),
                        'sales' => rand(30, 100),
                        'mindset' => rand(30, 100)
                    ]
                ]
            );

            // Generate 14 days of history for each user
            for ($i = 14; $i >= 0; $i--) {
                $date = now()->subDays($i)->toDateString();
                
                // For the last 7 days, we create actual activity records and sync the score
                $dailyTotal = 0;
                $consciousSum = 0;
                $subcoSum = 0;
                $activityCount = 0;

                if ($i < 7) {
                    $possibleActivities = [
                        ['title' => 'Visualization', 'cat' => 'subconscious', 'pts' => 8, 'type' => 'visualization', 'tier' => 'Consultant'],
                        ['title' => 'Affirmations', 'cat' => 'subconscious', 'pts' => 6, 'type' => 'affirmations', 'tier' => 'Consultant'],
                        ['title' => 'Audio Reprogramming', 'cat' => 'subconscious', 'pts' => 6, 'type' => 'audio_reprogramming', 'tier' => 'Rainmaker'],
                        ['title' => 'Belief Exercise', 'cat' => 'subconscious', 'pts' => 8, 'type' => 'belief_exercise', 'tier' => 'Titan'],
                        ['title' => 'Identity Statement', 'cat' => 'subconscious', 'pts' => 5, 'type' => 'identity_statement', 'tier' => 'Titan'],
                        ['title' => 'Cold Calling', 'cat' => 'conscious', 'pts' => 8, 'type' => 'cold_calling', 'tier' => 'Consultant'],
                        ['title' => 'Content Creation', 'cat' => 'conscious', 'pts' => 8, 'type' => 'content_creation', 'tier' => 'Consultant'],
                        ['title' => 'DM Conversations', 'cat' => 'conscious', 'pts' => 6, 'type' => 'dm_convos', 'tier' => 'Rainmaker'],
                        ['title' => 'Client Meetings', 'cat' => 'conscious', 'pts' => 10, 'type' => 'client_meetings', 'tier' => 'Rainmaker'],
                        ['title' => 'Deal Negotiation', 'cat' => 'conscious', 'pts' => 10, 'type' => 'negotiation', 'tier' => 'Titan'],
                        ['title' => 'CRM Update', 'cat' => 'conscious', 'pts' => 5, 'type' => 'crm_update', 'tier' => 'Consultant'],
                        ['title' => 'Site Visits', 'cat' => 'conscious', 'pts' => 10, 'type' => 'site_visits', 'tier' => 'Titan'],
                        ['title' => 'Luxury Outreach', 'cat' => 'conscious', 'pts' => 15, 'type' => 'luxury_outreach', 'tier' => 'Titan'],
                    ];

                    // Select 3-5 random activities for this day
                    $dailySet = array_rand($possibleActivities, rand(3, 5));
                    if (!is_array($dailySet)) $dailySet = [$dailySet];

                    foreach ($dailySet as $actIdx) {
                        $actData = $possibleActivities[$actIdx];
                        $isComp = rand(0, 10) > 2; // 80% completion rate for seed data
                        
                        \App\Models\Activity::create([
                            'user_id' => $user->id,
                            'title' => $actData['title'],
                            'type' => $actData['type'],
                            'category' => $actData['cat'],
                            'points' => $actData['pts'],
                            'min_tier' => $actData['tier'],
                            'scheduled_at' => $date . ' ' . rand(9, 17) . ':00:00',
                            'is_completed' => $isComp,
                            'completed_at' => $isComp ? $date . ' ' . rand(10, 18) . ':00:00' : null,
                        ]);

                        if ($isComp) {
                            $dailyTotal += $actData['pts'];
                            if ($actData['cat'] === 'conscious') $consciousSum += 100;
                            else $subcoSum += 100;
                        }
                        $activityCount++;
                    }
                } else {
                    $dailyTotal = rand(40, 90);
                }

                $conScore = $activityCount > 0 ? floor(($consciousSum / $activityCount)) : rand(60, 90);
                $subScore = $activityCount > 0 ? floor(($subcoSum / $activityCount)) : rand(60, 90);

                // Metrics
                \App\Models\PerformanceMetric::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $date],
                    [
                        'subconscious_score' => $subScore,
                        'conscious_score' => $conScore,
                        'results_score' => rand(30, 90),
                        'total_momentum_score' => $dailyTotal,
                        'leads_generated' => rand(0, 15),
                        'deals_closed' => rand(0, 3),
                        'commission_earned' => rand(0, 10000),
                        'streak_count' => rand(1, 20)
                    ]
                );
            }
        }
        // Seed Learning Content
        if (\Illuminate\Support\Facades\DB::table('learning_content')->count() === 0) {
            $learningContent = [
                // Market Fundamentals
                [
                    'title' => 'Dubai Market Overview 2026',
                    'description' => 'A strategic look at the upcoming year in real estate.',
                    'category' => 'marketFundamentals',
                    'type' => 'video',
                    'tier' => 'free',
                    'duration_minutes' => 45,
                ],
                [
                    'title' => 'The Blueprint Checklist',
                    'description' => 'Essential documents for every transaction.',
                    'category' => 'marketFundamentals',
                    'type' => 'article',
                    'tier' => 'free',
                    'duration_minutes' => 10,
                ],
                // Lead Systems
                [
                    'title' => 'Automated Lead Magnets',
                    'description' => 'How to build systems that attract clients while you sleep.',
                    'category' => 'leadSystems',
                    'type' => 'video',
                    'tier' => 'free',
                    'duration_minutes' => 30,
                ],
                // HNI Handling (Premium)
                [
                    'title' => 'The Billionaire Code',
                    'description' => 'Ethical psychology for working with HNIs.',
                    'category' => 'hniHandling',
                    'type' => 'video',
                    'tier' => 'premium',
                    'duration_minutes' => 60,
                ],
                [
                    'title' => 'Private Client Group Protocol',
                    'description' => 'The exclusive service standard for top-tier clients.',
                    'category' => 'hniHandling',
                    'type' => 'audio',
                    'tier' => 'premium',
                    'duration_minutes' => 25,
                ],
                // Commission Scaling
                [
                    'title' => 'Negotiating the 5%',
                    'description' => 'Never drop your commission again.',
                    'category' => 'commissionScaling',
                    'type' => 'video',
                    'tier' => 'premium',
                    'duration_minutes' => 40,
                ],
            ];

            foreach ($learningContent as $content) {
                \Illuminate\Support\Facades\DB::table('learning_content')->insert(array_merge($content, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // Seed Badges
        (new \App\Services\BadgeService())->seedDefaultBadges();

        // Seed Results data for users with history
        $allUsers = User::all();
        foreach ($allUsers as $user) {
            // Skip if already has results
            if (\App\Models\Result::where('user_id', $user->id)->exists()) continue;

            $resultTypes = ['hot_lead', 'deal_closed', 'commission'];
            $sources = ['bayut', 'property_finder', 'instagram', 'referral', 'cold_call'];
            $clientNames = ['Ahmed Al Maktoum', 'Sara Khan', 'John Miller', 'Fatima Hassan', 'Raj Patel', 'Maria Santos'];
            $properties = ['Marina Tower 2BR', 'Palm Jumeirah Villa', 'Downtown Studio', 'JLT 1BR', 'Business Bay 3BR', 'Creek Harbour Penthouse'];

            // Generate 5-15 results per user over the last 60 days
            $resultCount = rand(5, 15);
            for ($r = 0; $r < $resultCount; $r++) {
                $type = $resultTypes[array_rand($resultTypes)];
                $daysAgo = rand(0, 60);
                $date = now()->subDays($daysAgo)->toDateString();
                $value = $type === 'hot_lead' ? 0 : ($type === 'deal_closed' ? rand(5000, 50000) : rand(10000, 200000));

                \App\Models\Result::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'type' => $type,
                    'client_name' => $clientNames[array_rand($clientNames)],
                    'property_name' => $properties[array_rand($properties)],
                    'source' => $sources[array_rand($sources)],
                    'value' => $value,
                    'notes' => $type === 'deal_closed' ? 'Closed successfully' : null,
                ]);
            }

            // Generate 2-5 follow-ups
            $followUpCount = rand(2, 5);
            for ($f = 0; $f < $followUpCount; $f++) {
                $dueIn = rand(-3, 7); // some overdue, some upcoming
                \App\Models\FollowUp::create([
                    'user_id' => $user->id,
                    'result_id' => null,
                    'client_name' => $clientNames[array_rand($clientNames)],
                    'phone' => '+971' . rand(50, 58) . rand(1000000, 9999999),
                    'notes' => 'Follow up on property interest',
                    'priority' => rand(1, 3),
                    'due_at' => now()->addDays($dueIn),
                    'is_completed' => $dueIn < -1 ? (rand(0, 1) ? true : false) : false,
                    'completed_at' => null,
                ]);
            }
        }

        // Seed Weekly Scores
        foreach ($allUsers as $user) {
            if (\App\Models\WeeklyScore::where('user_id', $user->id)->exists()) continue;

            for ($w = 4; $w >= 0; $w--) {
                $weekStart = now()->subWeeks($w)->startOfWeek()->toDateString();
                $weekEnd = now()->subWeeks($w)->endOfWeek()->toDateString();

                \App\Models\WeeklyScore::create([
                    'user_id' => $user->id,
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'avg_momentum_score' => rand(40, 95) + (rand(0, 99) / 100),
                    'total_deals' => rand(0, 5),
                    'total_leads' => rand(2, 20),
                    'total_commission' => rand(0, 100000),
                    'days_active' => rand(3, 7),
                    'consistency_percentage' => rand(50, 100),
                ]);
            }
        }
    }
}

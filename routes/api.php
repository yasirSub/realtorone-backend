<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Helper function to get authenticated user
function getAuthUser(Request $request) {
    $token = $request->bearerToken();
    if (!$token) return null;
    return User::where('remember_token', $token)->first();
}

function seedDefaultActivityTypes() {
    // Identity Conditioning: Min 2 activities, Max Score 40 (from diagram)
    $identityKeys = ['journaling', 'webinar', 'visualization', 'affirmations', 'inner_game_audio', 'guided_reset'];
    \App\Models\ActivityType::where('category', 'subconscious')->whereNotIn('type_key', $identityKeys)->delete();

    $types = [
        // 1. Manual Identity Activities
        ['name' => 'Journaling', 'points' => 4, 'category' => 'subconscious', 'type_key' => 'journaling', 'icon' => 'BookHeart'],
        ['name' => 'Webinar', 'points' => 12, 'category' => 'subconscious', 'type_key' => 'webinar', 'icon' => 'Video'],
        // 2. Verified Identity Activities
        ['name' => 'Visualization', 'points' => 10, 'category' => 'subconscious', 'type_key' => 'visualization', 'icon' => 'Eye'],
        ['name' => 'Affirmations', 'points' => 8, 'category' => 'subconscious', 'type_key' => 'affirmations', 'icon' => 'Repeat'],
        ['name' => 'Inner Game Audio', 'points' => 8, 'category' => 'subconscious', 'type_key' => 'inner_game_audio', 'icon' => 'Headphones'],
        ['name' => 'Guided Reset', 'points' => 6, 'category' => 'subconscious', 'type_key' => 'guided_reset', 'icon' => 'Wind'],

        // Conscious (Part B)
        ['name' => 'Cold Calling', 'points' => 8, 'category' => 'conscious', 'type_key' => 'cold_calling', 'icon' => 'Phone'],
        ['name' => 'Content Creation', 'points' => 8, 'category' => 'conscious', 'type_key' => 'content_creation', 'icon' => 'Camera'],
        ['name' => 'Content Posting', 'points' => 6, 'category' => 'conscious', 'type_key' => 'content_posting', 'icon' => 'Share2'],
        ['name' => 'DM Conversations', 'points' => 6, 'category' => 'conscious', 'type_key' => 'dm_conversations', 'icon' => 'MessageCircle'],
        ['name' => 'WhatsApp Broadcast', 'points' => 6, 'category' => 'conscious', 'type_key' => 'whatsapp_broadcast', 'icon' => 'Send'],
        ['name' => 'Mass Emailing', 'points' => 6, 'category' => 'conscious', 'type_key' => 'mass_emailing', 'icon' => 'Mail'],
        ['name' => 'Client Meetings', 'points' => 10, 'category' => 'conscious', 'type_key' => 'client_meetings', 'icon' => 'Users'],
        ['name' => 'Prospecting', 'points' => 8, 'category' => 'conscious', 'type_key' => 'prospecting', 'icon' => 'Search'],
        ['name' => 'Follow-ups', 'points' => 8, 'category' => 'conscious', 'type_key' => 'follow_ups', 'icon' => 'RefreshCw'],
        ['name' => 'Deal Negotiation', 'points' => 10, 'category' => 'conscious', 'type_key' => 'deal_negotiation', 'icon' => 'Briefcase'],
        ['name' => 'Client Servicing', 'points' => 6, 'category' => 'conscious', 'type_key' => 'client_servicing', 'icon' => 'HeartHandshake'],
        ['name' => 'CRM Update', 'points' => 5, 'category' => 'conscious', 'type_key' => 'crm_update', 'icon' => 'Database'],
        ['name' => 'Site Visits', 'points' => 10, 'category' => 'conscious', 'type_key' => 'site_visits', 'icon' => 'MapPin'],
        ['name' => 'Referral Ask', 'points' => 6, 'category' => 'conscious', 'type_key' => 'referral_ask', 'icon' => 'UserPlus'],
        ['name' => 'Skill Training', 'points' => 8, 'category' => 'conscious', 'type_key' => 'skill_training', 'icon' => 'Zap'],
    ];

    foreach ($types as $type) {
        \App\Models\ActivityType::updateOrCreate(
            ['type_key' => $type['type_key']],
            array_merge($type, ['is_global' => true])
        );
    }
}

Route::get('/health', function () {
    $dbOk = true;
    $dbError = null;

    try {
        DB::connection()->getPdo();
    } catch (\Throwable $error) {
        $dbOk = false;
        $dbError = $error->getMessage();
    }

    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'db' => [
            'ok' => $dbOk,
            'error' => $dbError,
        ],
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/admin/stats', function () {
    try {
        $userCount = \App\Models\User::count();
        // Check activities table safely
        $activityCount = 0;
        $activeToday = 0;
        try {
            $activityCount = \Illuminate\Support\Facades\DB::table('activities')->count();
            $activeToday = \Illuminate\Support\Facades\DB::table('activities')->whereDate('created_at', now()->toDateString())->count();
        } catch (\Throwable $e) { }

        return response()->json([
            'total_users' => $userCount,
            'active_today' => $activeToday,
            'total_activities' => $activityCount,
            'db_connected' => true,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'total_users' => 0,
            'total_activities' => 0,
            'db_connected' => false,
            'error' => $e->getMessage()
        ]);
    }
});

Route::get('/admin/users', function () {
    $users = \App\Models\User::orderBy('created_at', 'desc')->get();
    
    // Attach momentum data to each user
    $today = now()->toDateString();
    $users->each(function ($user) use ($today) {
        $metric = \App\Models\PerformanceMetric::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        $user->daily_score = $metric ? $metric->total_momentum_score : 0;
        $user->today_subconscious = $metric ? $metric->subconscious_score : 0;
        $user->today_conscious = $metric ? $metric->conscious_score : 0;
        
        // Rank badge based on streak
        $streak = $user->current_streak ?? 0;
        if ($streak >= 30) {
            $user->momentum_badge = 'Market Operator';
            $user->badge_color = '#f59e0b';
        } elseif ($streak >= 14) {
            $user->momentum_badge = 'Momentum Builder';
            $user->badge_color = '#6d28d9';
        } elseif ($streak >= 7) {
            $user->momentum_badge = 'Rising Agent';
            $user->badge_color = '#10b981';
        } elseif ($streak >= 3) {
            $user->momentum_badge = 'Getting Started';
            $user->badge_color = '#3b82f6';
        } else {
            $user->momentum_badge = 'Inactive';
            $user->badge_color = '#6b7280';
        }
    });
    
    return response()->json($users);
});

Route::post('/admin/users/{id}/toggle-status', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    $user->status = ($user->status === 'inactive') ? 'active' : 'inactive';
    $user->save();
    return response()->json(['status' => 'ok', 'new_status' => $user->status]);
});

Route::get('/admin/users/{id}/performance', function ($id) {
    $metrics = \App\Models\PerformanceMetric::where('user_id', $id)
        ->orderBy('date', 'desc')
        ->limit(60) // Increased limit to support monthly/weekly views
        ->get();
    return response()->json(['success' => true, 'data' => $metrics]);
});

Route::get('/admin/users/{id}/activities', function ($id) {
    $activities = \App\Models\Activity::where('user_id', $id)
        ->orderBy('scheduled_at', 'desc')
        ->limit(150)
        ->get();
    return response()->json(['success' => true, 'data' => $activities]);
});

Route::delete('/admin/users/{id}', function ($id) {
    $user = \App\Models\User::findOrFail($id);
    $user->delete();
    return response()->json(['status' => 'ok']);
});

// Subscription Packages
Route::get('/admin/packages', function () {
    return response()->json(['success' => true, 'data' => \App\Models\SubscriptionPackage::orderBy('tier_level', 'asc')->get()]);
});

Route::post('/admin/packages', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string',
        'tier_level' => 'required|integer',
        'price_monthly' => 'required|numeric',
        'description' => 'nullable|string',
        'features' => 'nullable|array',
    ]);
    $package = \App\Models\SubscriptionPackage::create($data);
    return response()->json(['success' => true, 'data' => $package]);
});

Route::put('/admin/packages/{id}', function (Request $request, $id) {
    $package = \App\Models\SubscriptionPackage::findOrFail($id);
    $data = $request->validate([
        'name' => 'sometimes|string',
        'tier_level' => 'sometimes|integer',
        'price_monthly' => 'sometimes|numeric',
        'description' => 'nullable|string',
        'features' => 'nullable|array',
        'is_active' => 'sometimes|boolean',
    ]);
    $package->update($data);
    return response()->json(['success' => true, 'data' => $package]);
});

Route::delete('/admin/packages/{id}', function ($id) {
    $package = \App\Models\SubscriptionPackage::findOrFail($id);
    $package->delete();
    return response()->json(['success' => true]);
});

// User Subscriptions (History & Active)
Route::get('/admin/subscriptions', function () {
    $subs = \App\Models\UserSubscription::with(['user', 'package', 'coupon'])
        ->orderBy('created_at', 'desc')
        ->get();
    return response()->json(['success' => true, 'data' => $subs]);
});

// Coupons Management
Route::get('/admin/coupons', function () {
    return response()->json(['success' => true, 'data' => \App\Models\Coupon::orderBy('created_at', 'desc')->get()]);
});

Route::post('/admin/coupons', function (Request $request) {
    $data = $request->validate([
        'code' => 'required|string|unique:coupons,code',
        'discount_percentage' => 'required|integer|min:1|max:100',
        'expires_at' => 'nullable|date',
        'max_uses' => 'nullable|integer|min:1',
    ]);
    $coupon = \App\Models\Coupon::create($data);
    return response()->json(['success' => true, 'data' => $coupon]);
});

Route::delete('/admin/coupons/{id}', function ($id) {
    $coupon = \App\Models\Coupon::findOrFail($id);
    $coupon->delete();
    return response()->json(['success' => true]);
});

// Courses Management
Route::resource('admin/courses', \App\Http\Controllers\CourseController::class);

// User-facing Courses List
Route::get('/courses', function (Request $request) {
    $user = getAuthUser($request);
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    // Get current active subscription to determine tier rank
    $sub = \App\Models\UserSubscription::with('package')
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->where('expires_at', '>', now())
        ->orderBy('expires_at', 'desc')
        ->first();

    $userTierName = $sub ? $sub->package->name : 'Consultant';
    
    // Mission Tier Hierarchy
    $tierRanks = [
        'consultant' => 0,
        'rainmaker' => 1,
        'titan' => 2
    ];

    $userTierKey = strtolower($userTierName);
    $currentRank = $tierRanks[$userTierKey] ?? 0;

    // Get user's course progress
    $userProgress = DB::table('course_progress')
        ->where('user_id', $user->id)
        ->get()
        ->keyBy('course_id');

    // Get all courses ordered by module and sequence
    $allCourses = \App\Models\Course::orderBy('module_number')
        ->orderBy('sequence')
        ->get();

    // Group courses by module
    $modules = [];
    $moduleTierRequirements = [
        1 => 'consultant',  // Module 1: All tiers
        2 => 'rainmaker',   // Module 2: Rainmaker+
        3 => 'titan'        // Module 3: Titan only
    ];

    foreach ($allCourses as $course) {
        $moduleNum = $course->module_number ?? 1;
        $courseRank = $tierRanks[strtolower($course->min_tier)] ?? 0;
        
        // Check if module is accessible based on tier
        $moduleRequiredTier = $moduleTierRequirements[$moduleNum] ?? 'consultant';
        $moduleRequiredRank = $tierRanks[$moduleRequiredTier] ?? 0;
        $isModuleLocked = $currentRank < $moduleRequiredRank;
        
        // Check if previous module is completed (for sequential unlocking)
        $isSequentiallyLocked = false;
        if ($moduleNum > 1) {
            $prevModuleNum = $moduleNum - 1;
            $prevModuleCourses = $allCourses->where('module_number', $prevModuleNum);
            $prevModuleCompleted = $prevModuleCourses->every(function($prevCourse) use ($userProgress) {
                $progress = $userProgress->get($prevCourse->id);
                return $progress && $progress->is_completed;
            });
            $isSequentiallyLocked = !$prevModuleCompleted && $prevModuleCourses->isNotEmpty();
        }
        
        // Course is locked if: tier locked OR module locked OR sequentially locked
        $isLocked = ($courseRank > $currentRank) || $isModuleLocked || $isSequentiallyLocked;
        
        $progress = $userProgress->get($course->id);
        
        if (!isset($modules[$moduleNum])) {
            $modules[$moduleNum] = [
                'module_number' => $moduleNum,
                'module_name' => "Module $moduleNum",
                'is_locked' => $isModuleLocked || $isSequentiallyLocked,
                'required_tier' => ucfirst($moduleRequiredTier),
                'courses' => []
            ];
        }
        
        $modules[$moduleNum]['courses'][] = [
            'id' => $course->id,
            'title' => $course->title,
            'description' => $course->description,
            'url' => $course->url,
            'min_tier' => $course->min_tier,
            'module_number' => $moduleNum,
            'sequence' => $course->sequence ?? 0,
            'is_locked' => $isLocked,
            'progress_percent' => $progress ? $progress->progress_percent : 0,
            'is_completed' => $progress ? (bool)$progress->is_completed : false,
        ];
    }

    // Convert to indexed array
    $modulesArray = array_values($modules);

    return response()->json([
        'success' => true, 
        'data' => $modulesArray,
        'user_tier' => $userTierName
    ]);
});

// Update course progress
Route::post('/courses/{id}/progress', function (Request $request, $id) {
    $user = getAuthUser($request);
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $validated = $request->validate([
        'progress_percent' => 'required|integer|min:0|max:100',
        'is_completed' => 'nullable|boolean'
    ]);

    $progress = DB::table('course_progress')
        ->updateOrInsert(
            ['user_id' => $user->id, 'course_id' => $id],
            [
                'progress_percent' => $validated['progress_percent'],
                'is_completed' => $validated['is_completed'] ?? ($validated['progress_percent'] >= 100),
                'completed_at' => ($validated['is_completed'] ?? ($validated['progress_percent'] >= 100)) ? now() : null,
                'last_accessed_at' => now(),
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())')
            ]
        );

    return response()->json(['success' => true, 'message' => 'Progress updated']);
});

// Add this to the protected group
Route::get('/user/rewards', function (Request $request) {
    $user = getAuthUser($request);
    if (!$user) return response()->json(['success' => false], 401);

    $totalPoints = DB::table('activities')
        ->where('user_id', $user->id)
        ->where('is_completed', true)
        ->sum('points');

    $breakdown = DB::table('activities')
        ->where('user_id', $user->id)
        ->where('is_completed', true)
        ->select('category', DB::raw('sum(points) as total'))
        ->groupBy('category')
        ->get();

    return response()->json([
        'success' => true,
        'total_rewards' => (int) $totalPoints,
        'breakdown' => $breakdown
    ]);
});

// Points history: all completed activities with date, activity name, and points
Route::get('/user/points-history', function (Request $request) {
    $user = getAuthUser($request);
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $limit = $request->get('limit', 100); // Default to last 100 entries
    $offset = $request->get('offset', 0);

    $history = DB::table('activities')
        ->where('user_id', $user->id)
        ->where('is_completed', true)
        ->whereNotNull('points')
        ->where('points', '>', 0)
        ->select(
            'id',
            'title',
            'type',
            'category',
            'points',
            DB::raw('DATE(completed_at) as date'),
            'completed_at'
        )
        ->orderBy('completed_at', 'desc')
        ->limit($limit)
        ->offset($offset)
        ->get();

    $totalPoints = DB::table('activities')
        ->where('user_id', $user->id)
        ->where('is_completed', true)
        ->whereNotNull('points')
        ->where('points', '>', 0)
        ->sum('points');

    return response()->json([
        'success' => true,
        'data' => $history,
        'total_points' => (int) $totalPoints,
        'count' => count($history),
    ]);
});

// User-facing packages list (for Flutter app)
Route::get('/packages', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Models\SubscriptionPackage::where('is_active', true)->orderBy('tier_level', 'asc')->get()
    ]);
});

// User-facing: Get my active subscription
Route::get('/user/subscription', function (Request $request) {
    $user = getAuthUser($request);
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

    $sub = \App\Models\UserSubscription::with('package')
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->where('expires_at', '>', now())
        ->orderBy('expires_at', 'desc')
        ->first();

    return response()->json([
        'success' => true,
        'data' => $sub,
        'is_premium' => (bool) $sub,
        'membership_tier' => $sub ? $sub->package->name : 'Consultant',
        'expires_at' => $sub ? $sub->expires_at->toIso8601String() : null,
    ]);
});

// User-facing Subscription Routes
Route::post('/subscriptions/validate-coupon', function (Request $request) {
    $code = $request->input('code');
    $coupon = \App\Models\Coupon::where('code', $code)
        ->where('is_active', true)
        ->where(function ($query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        })
        ->first();

    if (!$coupon) return response()->json(['success' => false, 'message' => 'Invalid or expired coupon']);
    if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) return response()->json(['success' => false, 'message' => 'Coupon usage limit reached']);

    return response()->json(['success' => true, 'data' => $coupon]);
});

Route::post('/subscriptions/purchase', function (Request $request) {
    // Purchase logic with auth
    $user = getAuthUser($request);
    if (!$user) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    $data = $request->validate([
        'package_id' => 'required|exists:subscription_packages,id',
        'months' => 'required|integer|min:1',
        'coupon_id' => 'nullable|exists:coupons,id',
        'payment_id' => 'required|string', // Simulated PayPal ID
    ]);

    $package = \App\Models\SubscriptionPackage::findOrFail($data['package_id']);
    
    // Calculate amount
    $amount = $package->price_monthly * $data['months'];
    if ($data['coupon_id']) {
        $coupon = \App\Models\Coupon::find($data['coupon_id']);
        $amount = $amount * (1 - ($coupon->discount_percentage / 100));
        $coupon->increment('used_count');
    }

    $sub = \App\Models\UserSubscription::create([
        'user_id' => $user->id,
        'package_id' => $package->id,
        'started_at' => now(),
        'expires_at' => now()->addMonths($data['months']),
        'status' => 'active',
        'payment_method' => 'paypal',
        'payment_id' => $data['payment_id'],
        'amount_paid' => $amount,
        'coupon_id' => $data['coupon_id'],
    ]);

    // Update user tier
    $user->update([
        'membership_tier' => $package->name,
        'is_premium' => true,
        'premium_expires_at' => $sub->expires_at
    ]);

    return response()->json(['success' => true, 'data' => $sub]);
});

// Admin settings for configurable user activity points
Route::get('/admin/settings/user-activity-points', function () {
    // Default 20 if not set
    $points = cache('user_activity_points', 20);
    return response()->json(['success' => true, 'points' => $points]);
});

Route::post('/admin/settings/user-activity-points', function (Request $request) {
    $data = $request->validate(['points' => 'required|integer|min:1|max:100']);
    cache(['user_activity_points' => $data['points']], now()->addYears(5));
    return response()->json(['success' => true, 'points' => $data['points']]);
});

Route::post('/admin/login', function (Request $request) {
    \Illuminate\Support\Facades\Log::info('Admin Login Attempt', ['email' => $request->email]);
    
    // FORCE UPDATE admin user for debug/access
    \App\Models\User::updateOrCreate(
        ['email' => 'admin@realtorone.com'],
        [
            'name' => 'Admin Operator',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]
    );

    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = \App\Models\User::where('email', $credentials['email'])->first();

    if ($user && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
        $token = bin2hex(random_bytes(32));
        $user->update(['remember_token' => $token]);
        
        \Illuminate\Support\Facades\Log::info('Admin Login Success', ['user_id' => $user->id]);
        
        // Attach momentum data
        $today = now()->toDateString();
        $metric = \App\Models\PerformanceMetric::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        
        $user->daily_score = $metric ? $metric->total_momentum_score : 0;
        
        return response()->json([
            'status' => 'ok',
            'token' => $token,
            'user' => $user
        ]);
    }

    \Illuminate\Support\Facades\Log::warning('Admin Login Failed', ['email' => $request->email]);

    return response()->json([
        'message' => 'Credentials mismatch. Error 401: Unauthorized access to system core.',
        'debug_hint' => 'Check if email is admin@realtorone.com'
    ], 401);
});

Route::get('/admin/momentum-leaders', function () {
    $leaders = \App\Models\PerformanceMetric::with('user')
        ->where('date', now()->toDateString())
        ->orderBy('total_momentum_score', 'desc')
        ->limit(10)
        ->get();
    
    return response()->json($leaders);
});

Route::get('/activity-types', function (Request $request) {
    seedDefaultActivityTypes();

    $user = getAuthUser($request);

    $query = \App\Models\ActivityType::where('is_global', true);
    if ($user) {
        $query->orWhere('user_id', $user->id);
    }
    $types = $query->orderBy('category')->orderBy('name')->get();

    // Identity Conditioning: append subcategory (manual | verified) for grouping in UI
    $identitySubcategory = [
        'journaling' => 'manual', 'webinar' => 'manual',
        'visualization' => 'verified', 'affirmations' => 'verified',
        'inner_game_audio' => 'verified', 'guided_reset' => 'verified',
    ];
    $data = $types->map(function ($t) use ($identitySubcategory) {
        $arr = $t->toArray();
        if ($t->category === 'subconscious' && isset($identitySubcategory[$t->type_key])) {
            $arr['subcategory'] = $identitySubcategory[$t->type_key];
        }
        return $arr;
    });

    return response()->json(['success' => true, 'data' => $data]);
});

Route::post('/activity-types', function (Request $request) {
    $user = getAuthUser($request);
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'icon' => 'nullable|string',
        'description' => 'nullable|string',
    ]);
    
    // Users can only create subconscious activities with admin-configurable points
    $userPoints = cache('user_activity_points', 20);
    $activityType = \App\Models\ActivityType::create([
        'name' => $data['name'],
        'points' => $userPoints,
        'category' => 'subconscious',
        'type_key' => Str::slug($data['name'], '_'),
        'icon' => $data['icon'] ?? 'Activity',
        'description' => $data['description'] ?? '',
        'user_id' => $user->id,
        'is_global' => false,
    ]);
    
    return response()->json(['success' => true, 'data' => $activityType]);
});

Route::post('/admin/activity-types', function (Request $request) {
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'points' => 'required|integer|min:1',
        'category' => 'required|in:conscious,subconscious',
        'icon' => 'nullable|string',
        'min_tier' => 'nullable|string',
    ]);

    $activityType = \App\Models\ActivityType::create([
        'name' => $data['name'],
        'points' => $data['points'],
        'category' => $data['category'],
        'type_key' => Str::slug($data['name'], '_'),
        'icon' => $data['icon'] ?? 'Activity',
        'is_global' => true,
        'min_tier' => $data['min_tier'] ?? 'Consultant',
    ]);

    return response()->json(['success' => true, 'data' => $activityType]);
});

Route::put('/admin/activity-types/{id}', function (Request $request, $id) {
    $activityType = \App\Models\ActivityType::findOrFail($id);
    
    $data = $request->validate([
        'name' => 'sometimes|string|max:255',
        'points' => 'sometimes|integer|min:1',
        'category' => 'sometimes|in:conscious,subconscious',
        'icon' => 'nullable|string',
        'min_tier' => 'nullable|string',
    ]);
    
    $activityType->update($data);
    if (isset($data['name'])) {
        $activityType->update(['type_key' => Str::slug($data['name'], '_')]);
    }
    
    return response()->json(['success' => true, 'data' => $activityType->fresh()]);
});

Route::delete('/admin/activity-types/{id}', function ($id) {
    $activityType = \App\Models\ActivityType::findOrFail($id);
    $activityType->delete();
    return response()->json(['success' => true]);
});

Route::post('/register', function (Request $request) {
    $data = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:6'],
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
    ]);

    return response()->json([
        'status' => 'ok',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
    ], 201);
});

Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $data['email'])->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials.',
        ], 401);
    }

    // Generate token
    $token = bin2hex(random_bytes(32));
    $user->update(['remember_token' => $token]);

    return response()->json([
        'status' => 'ok',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
    ]);
});

Route::post('/logout', function (Request $request) {
    $token = $request->bearerToken();
    
    if ($token) {
        $user = User::where('remember_token', $token)->first();
        if ($user) {
            $user->update(['remember_token' => null]);
        }
    }

    return response()->json([
        'status' => 'ok',
        'message' => 'Logged out successfully',
    ]);
});

Route::post('/password/forgot', function (Request $request) {
    $data = $request->validate([
        'email' => ['required', 'email'],
    ]);

    $user = User::where('email', $data['email'])->first();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
        ], 404);
    }

    // Generate reset token
    $resetToken = bin2hex(random_bytes(32));
    $user->update(['remember_token' => $resetToken]);

    // In production, send email with reset link
    // For now, return token for testing
    return response()->json([
        'status' => 'ok',
        'message' => 'Password reset token generated',
        'reset_token' => $resetToken,
    ]);
});

Route::post('/password/reset', function (Request $request) {
    $data = $request->validate([
        'token' => ['required', 'string'],
        'password' => ['required', 'string', 'min:6'],
    ]);

    $user = User::where('remember_token', $data['token'])->first();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid or expired token.',
        ], 400);
    }

    $user->update([
        'password' => Hash::make($data['password']),
        'remember_token' => null,
    ]);

    return response()->json([
        'status' => 'ok',
        'message' => 'Password reset successfully',
    ]);
});

Route::post('/email/verify', function (Request $request) {
    $data = $request->validate([
        'email' => ['required', 'email'],
    ]);

    $user = User::where('email', $data['email'])->first();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found.',
        ], 404);
    }

    // Mark email as verified
    $user->update(['email_verified_at' => now()]);

    return response()->json([
        'status' => 'ok',
        'message' => 'Email verified successfully',
    ]);
});

// Protected routes (require authentication)
Route::group(['middleware' => []], function () {
    
    // ============== USER PROFILE ==============
    
    Route::get('/user/profile', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mobile' => $user->mobile,
                'city' => $user->city,
                'brokerage' => $user->brokerage,
                'instagram' => $user->instagram,
                'linkedin' => $user->linkedin,
                'years_experience' => $user->years_experience,
                'current_monthly_income' => $user->current_monthly_income,
                'target_monthly_income' => $user->target_monthly_income,
                'profile_photo' => $user->profile_photo_path ? url('storage/' . $user->profile_photo_path) : null,
                'is_profile_complete' => (bool) $user->is_profile_complete,
                'has_completed_diagnosis' => (bool) $user->has_completed_diagnosis,
                'diagnosis_blocker' => $user->diagnosis_blocker,
                'growth_score' => $user->growth_score ?? 0,
                'execution_rate' => $user->execution_rate ?? 0,
                'mindset_index' => $user->mindset_index ?? 0,
                'rank' => $user->rank,
                'current_streak' => $user->current_streak ?? 0,
                'is_premium' => (bool) $user->is_premium,
                'membership_tier' => $user->membership_tier ?? 'Consultant',
                'onboarding_step' => (int) ($user->onboarding_step ?? 0),
                'total_rewards' => (int) DB::table('activities')
                    ->where('user_id', $user->id)
                    ->where('is_completed', true)
                    ->sum('points'),
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
            ],
        ]);
    });

    Route::put('/user/profile', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:20'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'brokerage' => ['sometimes', 'nullable', 'string', 'max:255'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:100'],
            'linkedin' => ['sometimes', 'nullable', 'string', 'max:255'],
            'years_experience' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'current_monthly_income' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'target_monthly_income' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
        ]);
    });

    Route::put('/user/profile/setup', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'mobile' => ['required', 'string', 'max:20'],
            'city' => ['required', 'string', 'max:100'],
            'brokerage' => ['required', 'string', 'max:255'],
            'instagram' => ['sometimes', 'nullable', 'string', 'max:100'],
            'linkedin' => ['sometimes', 'nullable', 'string', 'max:255'],
            'years_experience' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'current_monthly_income' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'target_monthly_income' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'is_profile_complete' => ['sometimes', 'boolean'],
            'onboarding_step' => ['sometimes', 'integer'],
        ]);

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile setup completed',
        ]);
    });

    Route::post('/user/change-password', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    });

    Route::post('/user/photo', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('photo')->store('profile-photos', 'public');
        $user->update(['profile_photo_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully',
            'photo_url' => asset('storage/' . $path),
        ]);
    });

    // ============== DIAGNOSIS ==============
    
    Route::post('/diagnosis/submit', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $data = $request->validate([
            'primary_blocker' => ['required', 'string', 'in:leadGeneration,confidence,closing,discipline'],
            'scores' => ['required', 'array'],
        ]);

        $user->update([
            'has_completed_diagnosis' => true,
            'diagnosis_blocker' => $data['primary_blocker'],
            'diagnosis_scores' => json_encode($data['scores']),
            'growth_score' => 50, // Starting score
            'mindset_index' => 50,
            'rank' => 'Starter',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diagnosis submitted successfully',
            'data' => [
                'primary_blocker' => $data['primary_blocker'],
                'recommended_path' => $this->getRecommendedPath($data['primary_blocker']),
            ],
        ]);
    });

    // ============== ACTIVITIES ==============
    
    Route::get('/activities', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $date = $request->get('date', now()->toDateString());
        
        $activities = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', $date)
            ->orderBy('scheduled_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    });

    Route::post('/activities', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'type' => ['required', 'string'],
            'category' => ['required', 'string', 'in:conscious,subconscious,task'],
            'duration_minutes' => ['sometimes', 'integer', 'min:1'],
            'scheduled_at' => ['sometimes', 'date'],
        ]);

        $data['user_id'] = $user->id;
        $data['scheduled_at'] = $data['scheduled_at'] ?? now();

        $id = DB::table('activities')->insertGetId($data + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity created',
            'data' => ['id' => $id],
        ], 201);
    });

    Route::put('/activities/{id}/complete', function (Request $request, $id) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $activity = DB::table('activities')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$activity) {
            return response()->json(['success' => false, 'message' => 'Activity not found'], 404);
        }

        // Award points for this activity type when completing
        $service = new \App\Services\PerformanceService();
        $points = $service->getActivityPoints($activity->type);

        DB::table('activities')->where('id', $id)->update([
            'is_completed' => true,
            'completed_at' => now(),
            'points' => $points,
            'updated_at' => now(),
        ]);

        // Update streak
        $lastActivity = $user->last_activity_date;
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        if ($lastActivity === $yesterday || $lastActivity === null) {
            $user->increment('current_streak');
        } elseif ($lastActivity !== $today) {
            $user->update(['current_streak' => 1]);
        }

        $user->update([
            'last_activity_date' => $today,
            'execution_rate' => min(100, $user->execution_rate + 2),
        ]);

        // Recalculate daily score and award badges (keeps dashboard in sync)
        $metric = $service->calculateDailyScore($user);
        $badgeService = new \App\Services\BadgeService();
        $newBadges = $badgeService->checkAndAwardBadges($user);

        return response()->json([
            'success' => true,
            'message' => 'Activity completed',
            'current_streak' => $user->current_streak,
            'points_awarded' => $points,
            'daily_score' => $metric->total_momentum_score,
            'new_badges' => $newBadges,
        ]);
    });

    Route::get('/activities/progress', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $today = now()->toDateString();
        
        $tasksTotal = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', $today)
            ->where('category', 'task')
            ->count();
            
        $tasksCompleted = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', $today)
            ->where('category', 'task')
            ->where('is_completed', true)
            ->count();
            
        $subconsciousTotal = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', $today)
            ->where('category', 'subconscious')
            ->count();
            
        $subconsciousCompleted = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', $today)
            ->where('category', 'subconscious')
            ->where('is_completed', true)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $today,
                'tasks_total' => $tasksTotal,
                'tasks_completed' => $tasksCompleted,
                'subconscious_total' => $subconsciousTotal,
                'subconscious_completed' => $subconsciousCompleted,
                'current_streak' => $user->current_streak,
            ],
        ]);
    });

    Route::post('/activities/log', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'type' => ['required', 'string'],
            'category' => ['required', 'string', 'in:task,subconscious,conscious'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'value' => ['sometimes', 'numeric'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $service = new \App\Services\PerformanceService();
        $points = $service->getActivityPoints($data['type']);

        $activity = \App\Models\Activity::create([
            'user_id' => $user->id,
            'title' => ucwords(str_replace('_', ' ', $data['type'])),
            'type' => $data['type'],
            'category' => $data['category'],
            'points' => $points,
            'quantity' => $data['quantity'] ?? 1,
            'value' => $data['value'] ?? 0,
            'is_completed' => true,
            'completed_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $service->updateStreak($user);
        $metric = $service->calculateDailyScore($user);

        // Check and award badges
        $badgeService = new \App\Services\BadgeService();
        $newBadges = $badgeService->checkAndAwardBadges($user);

        return response()->json([
            'success' => true,
            'message' => 'Activity logged successfully',
            'data' => [
                'activity' => $activity,
                'daily_score' => $metric->total_momentum_score,
                'streak' => $user->current_streak,
                'new_badges' => $newBadges,
            ],
        ]);
    });

    Route::get('/dashboard/momentum', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $service = new \App\Services\PerformanceService();
        $metric = $service->calculateDailyScore($user);

        return response()->json([
            'success' => true,
            'data' => [
                'momentum_score' => $metric->total_momentum_score,
                'subconscious' => $metric->subconscious_score,
                'conscious' => $metric->conscious_score,
                'results' => $metric->results_score,
                'streak' => $user->current_streak,
                'income_logged' => $metric->commission_earned, // This will be from separate deal logs
            ],
        ]);
    });

    // ============== LEARNING ==============
    
    Route::get('/learning/categories', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $categories = [
            ['name' => 'marketFundamentals', 'title' => 'Market Fundamentals', 'tier' => 'free'],
            ['name' => 'leadSystems', 'title' => 'Lead Systems', 'tier' => 'free'],
            ['name' => 'communication', 'title' => 'Communication', 'tier' => 'free'],
            ['name' => 'negotiation', 'title' => 'Negotiation', 'tier' => 'free'],
            ['name' => 'hniHandling', 'title' => 'HNI Handling', 'tier' => 'premium'],
            ['name' => 'commissionScaling', 'title' => 'Commission Scaling', 'tier' => 'premium'],
            ['name' => 'dealArchitecture', 'title' => 'Deal Architecture', 'tier' => 'premium'],
            ['name' => 'brandAuthority', 'title' => 'Brand Authority', 'tier' => 'premium'],
        ];

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    });

    Route::get('/learning/content', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $category = $request->get('category');
        
        $query = DB::table('learning_content')
            ->where('is_active', true);
            
        if ($category) {
            $query->where('category', $category);
        }
        
        // If user is not premium, only show free content
        if (!$user->is_premium) {
            $query->where('tier', 'free');
        }

        $content = $query->orderBy('order')->get();

        // Get user progress
        $progress = DB::table('user_learning_progress')
            ->where('user_id', $user->id)
            ->pluck('progress_percent', 'learning_content_id');

        $content = $content->map(function ($item) use ($progress) {
            $item->progress_percent = $progress[$item->id] ?? 0;
            $item->is_completed = ($progress[$item->id] ?? 0) >= 100;
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $content,
        ]);
    });

    Route::post('/learning/progress', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'content_id' => ['required', 'integer', 'exists:learning_content,id'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        DB::table('user_learning_progress')->updateOrInsert(
            [
                'user_id' => $user->id,
                'learning_content_id' => $data['content_id'],
            ],
            [
                'progress_percent' => $data['progress_percent'],
                'is_completed' => $data['progress_percent'] >= 100,
                'last_accessed_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Progress updated',
        ]);
    });

    // ============== DASHBOARD ==============
    
    Route::get('/dashboard/stats', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();

        // Today's progress
        $todayTasks = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', $today)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN is_completed THEN 1 ELSE 0 END) as completed')
            ->first();

        // This week's stats
        $weekStats = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereBetween('scheduled_at', [$weekStart, now()])
            ->where('is_completed', true)
            ->selectRaw("
                SUM(CASE WHEN type = 'leadOutreach' THEN 1 ELSE 0 END) as calls,
                SUM(CASE WHEN type = 'meeting' THEN 1 ELSE 0 END) as meetings,
                SUM(CASE WHEN type = 'followUp' THEN 1 ELSE 0 END) as followups,
                SUM(CASE WHEN type = 'siteVisit' THEN 1 ELSE 0 END) as site_visits
            ")
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'growth_score' => $user->growth_score ?? 0,
                'execution_rate' => $user->execution_rate ?? 0,
                'mindset_index' => $user->mindset_index ?? 0,
                'rank' => $user->rank ?? 'Starter',
                'current_streak' => $user->current_streak ?? 0,
                'today' => [
                    'total' => $todayTasks->total ?? 0,
                    'completed' => $todayTasks->completed ?? 0,
                ],
                'this_week' => [
                    'calls' => $weekStats->calls ?? 0,
                    'meetings' => $weekStats->meetings ?? 0,
                    'followups' => $weekStats->followups ?? 0,
                    'site_visits' => $weekStats->site_visits ?? 0,
                ],
            ],
        ]);
    });
    // ============== REPORTS ==============

    Route::get('/reports/growth', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $period = $request->get('period', 'week'); // week, month, year
        $labels = [];
        $data = [];
        $today = now();

        if ($period === 'week') {
            for ($i = 6; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dateStr = $date->toDateString();
                $labels[] = $date->format('D'); // Mon, Tue

                $query = DB::table('activities')
                    ->where('user_id', $user->id)
                    ->whereDate('completed_at', $dateStr)
                    ->where('is_completed', true);
                
                $data[] = $query->count();
                $pointsData[] = (int) $query->sum('points');
            }
        } elseif ($period === 'month') {
            for ($i = 29; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dateStr = $date->toDateString();
                $labels[] = $date->format('d'); // 01, 02

                $query = DB::table('activities')
                    ->where('user_id', $user->id)
                    ->whereDate('completed_at', $dateStr)
                    ->where('is_completed', true);
                
                $data[] = $query->count();
                $pointsData[] = (int) $query->sum('points');
            }
        } elseif ($period === 'year') {
            for ($i = 11; $i >= 0; $i--) {
                $date = $today->copy()->subMonths($i);
                $month = $date->month;
                $year = $date->year;
                $labels[] = $date->format('M'); // Jan, Feb

                $query = DB::table('activities')
                    ->where('user_id', $user->id)
                    ->whereYear('completed_at', $year)
                    ->whereMonth('completed_at', $month)
                    ->where('is_completed', true);
                
                $data[] = $query->count();
                $pointsData[] = (int) $query->sum('points');
            }
        }

        // Get activity breakdown with points
        $breakdown = DB::table('activities')
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->where('completed_at', '>=', $period === 'week' ? now()->subDays(7) : ($period === 'month' ? now()->subDays(30) : now()->subYear()))
            ->select('type', DB::raw('count(*) as count'), DB::raw('sum(points) as total_points'))
            ->groupBy('type')
            ->get();

        return response()->json([
            'success' => true,
            'period' => $period,
            'labels' => $labels,
            'data' => $data,
            'points_data' => $pointsData ?? [],
            'breakdown' => $breakdown,
            'growth_score' => $user->growth_score ?? 0,
            'execution_rate' => $user->execution_rate ?? 0,
            'total_rewards' => (int) DB::table('activities')->where('user_id', $user->id)->where('is_completed', true)->sum('points')
        ]);
    });

    // ============== TASKS ==============

    Route::get('/tasks/today', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $today = now()->toDateString();
        
        // Get today's tasks/activities
        $tasks = DB::table('activities')
            ->where('user_id', $user->id)
            ->whereDate('scheduled_at', $today)
            ->select('id', 'title', 'description', 'is_completed', 'type')
            ->orderBy('is_completed', 'asc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate completion percentage
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('is_completed', true)->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return response()->json([
            'success' => true,
            'tasks' => $tasks->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'is_completed' => (bool) $task->is_completed,
                    'type' => $task->type,
                ];
            }),
            'total' => $totalTasks,
            'completed' => $completedTasks,
            'completion_rate' => $completionRate,
        ]);
    });

    // ============== RESULTS TRACKER (Phase 2) ==============
    
    // ----- CLIENTS (Results-based) -----
    // Simple wrapper endpoints so the app can ask:
    // 1) "Does this user have any clients yet?"
    // 2) "Create the first client" (stored as a hot_lead result)
    Route::get('/clients/status', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $count = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->whereNotNull('client_name')
            ->count();

        return response()->json([
            'success' => true,
            'has_clients' => $count > 0,
            'clients_count' => $count,
        ]);
    });

    Route::post('/clients', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'client_name' => ['required', 'string', 'max:255'],
            'property_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'string', 'in:active,converted,lost'],
        ]);

        $result = \App\Models\Result::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'type' => 'hot_lead',
            'client_name' => $data['client_name'],
            'property_name' => $data['property_name'] ?? null,
            'source' => $data['source'] ?? null,
            'value' => $data['value'] ?? 0,
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client created',
            'data' => $result,
        ], 201);
    });

    // Per-client revenue actions (Cold Call Block, Follow-up Block, etc.)
    Route::get('/clients/{id}/actions', function (Request $request, $id) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $client = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->findOrFail($id);

        $date = $request->get('date', now()->toDateString());
        if (!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = now()->toDateString();
        }

        $meta = [];
        if ($client->notes) {
            try {
                $decoded = json_decode($client->notes, true);
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            } catch (\Throwable $e) {
            }
        }

        $dailyActions = $meta['daily_actions'] ?? [];
        $storedActions = is_array($dailyActions) ? ($dailyActions[$date] ?? []) : [];

        // Backward compatibility: old structure stored in meta['actions']
        if (empty($storedActions) && isset($meta['actions']) && is_array($meta['actions']) && $date === now()->toDateString()) {
            $storedActions = $meta['actions'];
        }

        $config = [
            ['key' => 'cold_call_block', 'label' => 'Cold Calling Block'],
            ['key' => 'follow_up_block', 'label' => 'Follow-up Block'],
            ['key' => 'client_meeting', 'label' => 'Client Meeting'],
            ['key' => 'site_visit', 'label' => 'Site Visit'],
            ['key' => 'content_creation', 'label' => 'Content Creation'],
            ['key' => 'content_posting', 'label' => 'Content Posting'],
            ['key' => 'prospecting_session', 'label' => 'Prospecting Session'],
            ['key' => 'deal_negotiation', 'label' => 'Deal Negotiation'],
            ['key' => 'crm_update', 'label' => 'CRM Update'],
            ['key' => 'referral_ask', 'label' => 'Referral Ask'],
            ['key' => 'deal_closed', 'label' => 'Deal Closed'],
            ['key' => 'network_event', 'label' => 'Network Event'],
            ['key' => 'proposal_sent', 'label' => 'Proposal Sent'],
        ];

        $actions = array_map(function ($item) use ($storedActions) {
            $key = $item['key'];
            $status = $storedActions[$key] ?? null;
            return [
                'key' => $key,
                'label' => $item['label'],
                'status' => $status, // 'yes' | 'no' | null
            ];
        }, $config);

        return response()->json([
            'success' => true,
            'data' => [
                'client' => $client,
                'date' => $date,
                'actions' => $actions,
            ],
        ]);
    });

    Route::post('/clients/{id}/actions', function (Request $request, $id) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'action_key' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:yes,no'],
            'date' => ['sometimes', 'date'],
        ]);

        $client = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->findOrFail($id);

        $date = $data['date'] ?? now()->toDateString();

        $meta = [];
        if ($client->notes) {
            try {
                $decoded = json_decode($client->notes, true);
                if (is_array($decoded)) {
                    $meta = $decoded;
                }
            } catch (\Throwable $e) {
            }
        }

        $meta['daily_actions'] = $meta['daily_actions'] ?? [];
        if (!is_array($meta['daily_actions'])) {
            $meta['daily_actions'] = [];
        }
        $meta['daily_actions'][$date] = $meta['daily_actions'][$date] ?? [];
        if (!is_array($meta['daily_actions'][$date])) {
            $meta['daily_actions'][$date] = [];
        }
        $meta['daily_actions'][$date][$data['action_key']] = $data['status'];

        $client->notes = json_encode($meta);
        $client->save();

        return response()->json([
            'success' => true,
            'message' => 'Action updated',
        ]);
    });

    // ─── Save detailed action log for a client (cold call, site visit, negotiation, referral, deal closed) ───
    Route::post('/clients/{id}/action-log', function (Request $request, $id) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'action_type' => ['required', 'string', 'in:cold_call,site_visit,deal_negotiation,deal_closed,referral_ask'],
            'date'        => ['sometimes', 'date'],
            'payload'     => ['required', 'array'],
        ]);

        $client = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->findOrFail($id);

        $date = $data['date'] ?? now()->toDateString();
        $type = $data['action_type'];
        $payload = $data['payload'];

        $meta = [];
        if ($client->notes) {
            try {
                $decoded = json_decode($client->notes, true);
                if (is_array($decoded)) $meta = $decoded;
            } catch (\Throwable $e) {}
        }

        // Store under action_logs -> date -> type (array of entries for that day)
        $meta['action_logs'] = $meta['action_logs'] ?? [];
        $meta['action_logs'][$date] = $meta['action_logs'][$date] ?? [];
        $meta['action_logs'][$date][$type] = $meta['action_logs'][$date][$type] ?? [];
        $meta['action_logs'][$date][$type][] = array_merge($payload, [
            'logged_at' => now()->toIso8601String(),
        ]);

        // If deal_closed, also create a result record for the performance pipeline
        if ($type === 'deal_closed') {
            $dealValue = floatval($payload['deal_amount'] ?? 0);
            $commission = floatval($payload['commission'] ?? 0);

            if ($dealValue > 0) {
                \App\Models\Result::create([
                    'user_id'       => $user->id,
                    'type'          => 'deal_closed',
                    'client_name'   => $client->client_name,
                    'property_name' => $payload['deal_type'] ?? null,
                    'value'         => $dealValue,
                    'notes'         => json_encode([
                        'deal_type'        => $payload['deal_type'] ?? null,
                        'commission'       => $commission,
                        'parent_client_id' => $client->id,
                    ]),
                    'date' => $date,
                ]);
            }
        }

        $client->notes = json_encode($meta);
        $client->save();

        return response()->json([
            'success' => true,
            'message' => ucfirst(str_replace('_', ' ', $type)) . ' logged successfully',
        ]);
    });

    // ─── Get action logs for a client on a specific date ───
    Route::get('/clients/{id}/action-logs', function (Request $request, $id) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $client = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->findOrFail($id);

        $date = $request->query('date', now()->toDateString());

        $meta = [];
        if ($client->notes) {
            try {
                $decoded = json_decode($client->notes, true);
                if (is_array($decoded)) $meta = $decoded;
            } catch (\Throwable $e) {}
        }

        $logs = ($meta['action_logs'] ?? [])[$date] ?? [];

        return response()->json([
            'success' => true,
            'data'    => [
                'client_id' => $id,
                'date'      => $date,
                'logs'      => $logs,
            ],
        ]);
    });

    // Log a result (hot lead, deal closed, commission)
    Route::post('/results', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'type' => ['required', 'string', 'in:hot_lead,deal_closed,commission'],
            'client_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'property_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => ['sometimes', 'nullable', 'string', 'in:bayut,property_finder,instagram,referral,cold_call,walk_in,linkedin,other'],
            'value' => ['sometimes', 'numeric', 'min:0'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'date' => ['sometimes', 'date'],
        ]);

        $result = \App\Models\Result::create([
            'user_id' => $user->id,
            'date' => $data['date'] ?? now()->toDateString(),
            'type' => $data['type'],
            'client_name' => $data['client_name'] ?? null,
            'property_name' => $data['property_name'] ?? null,
            'source' => $data['source'] ?? null,
            'value' => $data['value'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        // Update performance metrics
        $service = new \App\Services\PerformanceService();
        $metric = $service->calculateDailyScore($user);

        // Check badges
        $badgeService = new \App\Services\BadgeService();
        $newBadges = $badgeService->checkAndAwardBadges($user);

        // Update user total commission
        if ($data['type'] === 'commission' || $data['type'] === 'deal_closed') {
            $totalCommission = \App\Models\Result::where('user_id', $user->id)
                ->whereIn('type', ['commission', 'deal_closed'])
                ->sum('value');
            $user->update(['total_commission' => $totalCommission]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Result logged successfully',
            'data' => [
                'result' => $result,
                'daily_score' => $metric->total_momentum_score,
                'results_score' => $metric->results_score,
                'new_badges' => $newBadges,
            ],
        ]);
    });

    // Get results (filterable by type, date range)
    Route::get('/results', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = \App\Models\Result::where('user_id', $user->id);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('from')) {
            $query->where('date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->where('date', '<=', $request->to);
        }

        $results = $query->orderByDesc('date')->orderByDesc('created_at')->get();

        // Summary
        $monthStart = now()->startOfMonth()->toDateString();
        $summary = [
            'hot_leads' => \App\Models\Result::where('user_id', $user->id)->where('type', 'hot_lead')->where('date', '>=', $monthStart)->count(),
            'deals_closed' => \App\Models\Result::where('user_id', $user->id)->where('type', 'deal_closed')->where('date', '>=', $monthStart)->count(),
            'total_commission' => (float) \App\Models\Result::where('user_id', $user->id)->whereIn('type', ['commission', 'deal_closed'])->where('date', '>=', $monthStart)->sum('value'),
            'conversion_rate' => 0,
        ];

        $totalLeads = \App\Models\Result::where('user_id', $user->id)->where('type', 'hot_lead')->where('date', '>=', $monthStart)->count();
        if ($totalLeads > 0) {
            $summary['conversion_rate'] = round(($summary['deals_closed'] / $totalLeads) * 100, 1);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
            'summary' => $summary,
        ]);
    });

    // ─── Revenue Tracker key metrics (Week / Month / Quarter) ───
    Route::get('/revenue/metrics', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $period = $request->query('period', 'month'); // week | month | quarter
        $now = now();

        switch ($period) {
            case 'week':
                $from = $now->copy()->startOfWeek()->toDateString();
                break;
            case 'quarter':
                $from = $now->copy()->firstOfQuarter()->toDateString();
                break;
            default: // month
                $from = $now->copy()->startOfMonth()->toDateString();
                break;
        }
        $to = $now->toDateString();

        $hotLeads = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->whereBetween('date', [$from, $to])
            ->count();

        $dealsClosed = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'deal_closed')
            ->whereBetween('date', [$from, $to])
            ->count();

        $totalCommission = (float) \App\Models\Result::where('user_id', $user->id)
            ->whereIn('type', ['commission', 'deal_closed'])
            ->whereBetween('date', [$from, $to])
            ->sum('value');

        // Top source: find the most common source among hot_leads
        $topSourceRow = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->whereBetween('date', [$from, $to])
            ->whereNotNull('source')
            ->selectRaw('source, COUNT(*) as cnt')
            ->groupBy('source')
            ->orderByDesc('cnt')
            ->first();

        $topSource = $topSourceRow ? $topSourceRow->source : null;

        // Previous period for comparison
        switch ($period) {
            case 'week':
                $prevFrom = $now->copy()->subWeek()->startOfWeek()->toDateString();
                $prevTo = $now->copy()->subWeek()->endOfWeek()->toDateString();
                break;
            case 'quarter':
                $prevFrom = $now->copy()->subQuarter()->firstOfQuarter()->toDateString();
                $prevTo = $now->copy()->subQuarter()->lastOfQuarter()->toDateString();
                break;
            default:
                $prevFrom = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $prevTo = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
        }

        $prevLeads = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'hot_lead')
            ->whereBetween('date', [$prevFrom, $prevTo])
            ->count();

        $prevDeals = \App\Models\Result::where('user_id', $user->id)
            ->where('type', 'deal_closed')
            ->whereBetween('date', [$prevFrom, $prevTo])
            ->count();

        $leadsChange = $prevLeads > 0 ? round((($hotLeads - $prevLeads) / $prevLeads) * 100) : 0;
        $dealsChange = $prevDeals > 0 ? round((($dealsClosed - $prevDeals) / $prevDeals) * 100) : 0;

        // Recent activity (last 10 results of any type in this period)
        $recentActivity = \App\Models\Result::where('user_id', $user->id)
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'type', 'client_name', 'value', 'source', 'date', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'from' => $from,
                'to' => $to,
                'hot_leads' => $hotLeads,
                'deals_closed' => $dealsClosed,
                'total_commission' => $totalCommission,
                'top_source' => $topSource,
                'leads_change' => $leadsChange,
                'deals_change' => $dealsChange,
                'recent_activity' => $recentActivity,
            ],
        ]);
    });

    // Monthly results graph data
    Route::get('/results/monthly-graph', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth()->toDateString();
            $monthEnd = $date->copy()->endOfMonth()->toDateString();

            $leads = \App\Models\Result::where('user_id', $user->id)
                ->where('type', 'hot_lead')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->count();

            $deals = \App\Models\Result::where('user_id', $user->id)
                ->where('type', 'deal_closed')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->count();

            $commission = \App\Models\Result::where('user_id', $user->id)
                ->whereIn('type', ['commission', 'deal_closed'])
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('value');

            $months[] = [
                'label' => $date->format('M'),
                'leads' => $leads,
                'deals' => $deals,
                'commission' => (float) $commission,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $months,
        ]);
    });

    // ============== FOLLOW-UP DISCIPLINE GUARD (Phase 2) ==============

    Route::post('/follow-ups', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'result_id' => ['sometimes', 'nullable', 'integer'],
            'client_name' => ['required', 'string', 'max:255'],
            'contact_info' => ['sometimes', 'nullable', 'string', 'max:255'],
            'due_at' => ['required', 'date'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'priority' => ['sometimes', 'integer', 'in:1,2,3'],
        ]);

        $followUp = \App\Models\FollowUp::create([
            'user_id' => $user->id,
            'result_id' => $data['result_id'] ?? null,
            'client_name' => $data['client_name'],
            'contact_info' => $data['contact_info'] ?? null,
            'due_at' => $data['due_at'],
            'notes' => $data['notes'] ?? null,
            'priority' => $data['priority'] ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up created',
            'data' => $followUp,
        ]);
    });

    Route::get('/follow-ups', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Mark overdue ones
        \App\Models\FollowUp::where('user_id', $user->id)
            ->where('is_completed', false)
            ->where('due_at', '<', now())
            ->update(['is_overdue' => true]);

        $pending = \App\Models\FollowUp::where('user_id', $user->id)
            ->where('is_completed', false)
            ->orderBy('priority', 'desc')
            ->orderBy('due_at')
            ->get();

        $completed = \App\Models\FollowUp::where('user_id', $user->id)
            ->where('is_completed', true)
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        $overdueCount = $pending->where('is_overdue', true)->count();

        return response()->json([
            'success' => true,
            'data' => [
                'pending' => $pending,
                'completed' => $completed,
                'overdue_count' => $overdueCount,
                // Follow-up Discipline Guard: alert message if overdue
                'guard_alert' => $overdueCount > 0
                    ? "⚠️ You have {$overdueCount} overdue follow-up(s). Hot leads cool down fast — reach out now!"
                    : null,
            ],
        ]);
    });

    Route::put('/follow-ups/{id}/complete', function (Request $request, $id) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $followUp = \App\Models\FollowUp::where('user_id', $user->id)->findOrFail($id);
        $followUp->update([
            'is_completed' => true,
            'completed_at' => now(),
            'is_overdue' => false,
        ]);

        // Log as a follow_up activity for points
        $service = new \App\Services\PerformanceService();
        $points = $service->getActivityPoints('followUp');

        \App\Models\Activity::create([
            'user_id' => $user->id,
            'title' => "Follow-up: {$followUp->client_name}",
            'type' => 'followUp',
            'category' => 'conscious',
            'points' => $points,
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        $service->updateStreak($user);
        $metric = $service->calculateDailyScore($user);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up completed! Activity points earned.',
            'data' => [
                'daily_score' => $metric->total_momentum_score,
                'points_earned' => $points,
            ],
        ]);
    });

    // ============== LEADERBOARD (Phase 4) ==============

    Route::get('/leaderboard', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $category = $request->get('category', 'consistency');
        $period = $request->get('period', 'weekly');

        $service = new \App\Services\LeaderboardService();
        $data = $service->getLeaderboard($category, $period, $user->id);

        return response()->json([
            'success' => true,
            'category' => $category,
            'period' => $period,
            'data' => $data,
        ]);
    });

    Route::get('/leaderboard/categories', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => [
                ['key' => 'consistency', 'name' => 'Consistency Leaders', 'icon' => '📅', 'period' => 'weekly', 'description' => 'Who shows up every single day'],
                ['key' => 'momentum_climber', 'name' => 'Momentum Climbers', 'icon' => '🚀', 'period' => 'weekly', 'description' => 'Biggest score improvement this week'],
                ['key' => 'deal_maker', 'name' => 'Deal Makers', 'icon' => '🤝', 'period' => 'monthly', 'description' => 'Most deals closed this month'],
                ['key' => 'revenue', 'name' => 'Revenue Kings', 'icon' => '💰', 'period' => 'monthly', 'description' => 'Highest commission this month'],
                ['key' => 'identity_discipline', 'name' => 'Identity Masters', 'icon' => '🧠', 'period' => 'weekly', 'description' => 'Highest identity conditioning score'],
            ],
        ]);
    });

    // Refresh leaderboards (can be called by admin or cron)
    Route::post('/leaderboard/refresh', function (Request $request) {
        $service = new \App\Services\LeaderboardService();
        $service->refreshLeaderboards();

        return response()->json([
            'success' => true,
            'message' => 'Leaderboards refreshed',
        ]);
    });

    // ============== BADGES (Phase 4) ==============

    Route::get('/badges', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $service = new \App\Services\BadgeService();
        $badges = $service->getUserBadges($user->id);

        $earnedCount = $badges->where('earned', true)->count();
        $totalCount = $badges->count();

        return response()->json([
            'success' => true,
            'data' => [
                'badges' => $badges,
                'earned_count' => $earnedCount,
                'total_count' => $totalCount,
                'completion_percent' => $totalCount > 0 ? round(($earnedCount / $totalCount) * 100) : 0,
            ],
        ]);
    });

    Route::get('/badges/recent', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $recent = DB::table('user_badges')
            ->join('badges', 'badges.id', '=', 'user_badges.badge_id')
            ->where('user_badges.user_id', $user->id)
            ->orderByDesc('user_badges.earned_at')
            ->limit(5)
            ->select('badges.*', 'user_badges.earned_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recent,
        ]);
    });

    // ============== WEEKLY REVIEW (Phase 6 preview) ==============

    Route::get('/weekly-review', function (Request $request) {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        $metrics = \App\Models\PerformanceMetric::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $avgScore = $metrics->avg('total_momentum_score') ?? 0;
        $daysActive = $metrics->where('total_momentum_score', '>', 0)->count();

        $totalActivities = DB::table('activities')
            ->where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereBetween('completed_at', [$weekStart, now()])
            ->count();

        $results = \App\Models\Result::where('user_id', $user->id)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->get();

        $leads = $results->where('type', 'hot_lead')->count();
        $deals = $results->where('type', 'deal_closed')->count();
        $commission = $results->whereIn('type', ['commission', 'deal_closed'])->sum('value');

        // Personal best comparison
        $allTimeAvg = \App\Models\PerformanceMetric::where('user_id', $user->id)
            ->avg('total_momentum_score') ?? 0;

        $vsAverage = $allTimeAvg > 0 ? round((($avgScore - $allTimeAvg) / $allTimeAvg) * 100) : 0;

        // Motivational message (never negative)
        $message = $vsAverage > 0
            ? "🔥 You scored {$vsAverage}% higher than your personal average this week. Keep going!"
            : "💪 Every active day builds momentum. You showed up {$daysActive}/7 days this week.";

        return response()->json([
            'success' => true,
            'data' => [
                'week' => ['start' => $weekStart, 'end' => $weekEnd],
                'avg_score' => round($avgScore),
                'days_active' => $daysActive,
                'consistency_percent' => round(($daysActive / 7) * 100),
                'total_activities' => $totalActivities,
                'leads' => $leads,
                'deals' => $deals,
                'commission' => (float) $commission,
                'streak' => $user->current_streak,
                'vs_average_percent' => $vsAverage,
                'message' => $message,
            ],
        ]);
    });

});


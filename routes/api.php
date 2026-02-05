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
                'onboarding_step' => (int) ($user->onboarding_step ?? 0),
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
            'category' => ['required', 'string', 'in:task,subconscious'],
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

        DB::table('activities')->where('id', $id)->update([
            'is_completed' => true,
            'completed_at' => now(),
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

        return response()->json([
            'success' => true,
            'message' => 'Activity completed',
            'current_streak' => $user->current_streak,
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

                $count = DB::table('activities')
                    ->where('user_id', $user->id)
                    ->whereDate('completed_at', $dateStr)
                    ->where('is_completed', true)
                    ->count();
                $data[] = $count;
            }
        } elseif ($period === 'month') {
            // Last 30 days, maybe grouping by weeks or just days?
            // Let's do last 4 weeks for simplicity or every 5 days
            for ($i = 29; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);
                $dateStr = $date->toDateString();
                $labels[] = $date->format('d'); // 01, 02

                $count = DB::table('activities')
                    ->where('user_id', $user->id)
                    ->whereDate('completed_at', $dateStr)
                    ->where('is_completed', true)
                    ->count();
                $data[] = $count;
            }
        } elseif ($period === 'year') {
            for ($i = 11; $i >= 0; $i--) {
                $date = $today->copy()->subMonths($i);
                $month = $date->month;
                $year = $date->year;
                $labels[] = $date->format('M'); // Jan, Feb

                $count = DB::table('activities')
                    ->where('user_id', $user->id)
                    ->whereYear('completed_at', $year)
                    ->whereMonth('completed_at', $month)
                    ->where('is_completed', true)
                    ->count();
                $data[] = $count;
            }
        }

        return response()->json([
            'success' => true,
            'period' => $period,
            'labels' => $labels,
            'data' => $data,
            'growth_score' => $user->growth_score ?? 0,
            'execution_rate' => $user->execution_rate ?? 0,
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
            ->whereDate('due_date', $today)
            ->select('id', 'title', 'description', 'is_completed', 'activity_type')
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
                    'type' => $task->activity_type,
                ];
            }),
            'total' => $totalTasks,
            'completed' => $completedTasks,
            'completion_rate' => $completionRate,
        ]);
    });
});

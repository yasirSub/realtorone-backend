<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendPushBroadcastJob;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Services\NotificationScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationBroadcastController extends Controller
{
    private function resolveAdminUser(Request $request): ?User
    {
        $tokenUser = getAuthUser($request);
        if ($tokenUser) {
            return $tokenUser;
        }

        // Keep notifications aligned with current admin panel routes
        // that are accessible without strict token middleware.
        return User::query()->where('email', 'admin@realtorone.com')->first();
    }

    public function index(Request $request)
    {
        $user = $this->resolveAdminUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $q = NotificationBroadcast::query()->orderByDesc('id');
        if ($request->query('status')) {
            $q->where('status', $request->query('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $q->limit(200)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->resolveAdminUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'display_style' => 'required|in:standard,banner,silent',
            'audience' => 'required|in:all,tier,users',
            'tier' => 'nullable|in:Consultant,Rainmaker,Titan',
            'target_user_ids' => 'nullable|array',
            'target_user_ids.*' => 'integer',
            'scheduled_at' => 'nullable|date',
            'recurrence_type' => 'required|in:none,daily,weekly',
            'recurrence_time' => 'nullable|date_format:H:i',
            'recurrence_day_of_week' => 'nullable|integer|min:0|max:6',
            'timezone' => 'nullable|string|max:64',
            'deep_link' => 'nullable|string|max:512',
            'extra_data' => 'nullable|array',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $data = $v->validated();
        if ($data['audience'] === 'tier' && empty($data['tier'])) {
            return response()->json(['success' => false, 'message' => 'tier is required for tier audience'], 422);
        }
        if ($data['audience'] === 'users' && empty($data['target_user_ids'])) {
            return response()->json(['success' => false, 'message' => 'target_user_ids required for users audience'], 422);
        }

        $broadcast = NotificationBroadcast::query()->create([
            'title' => $data['title'],
            'body' => $data['body'],
            'display_style' => $data['display_style'],
            'audience' => $data['audience'],
            'tier' => $data['tier'] ?? null,
            'target_user_ids' => $data['target_user_ids'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'recurrence_type' => $data['recurrence_type'],
            'recurrence_time' => $data['recurrence_time'] ?? null,
            'recurrence_day_of_week' => $data['recurrence_day_of_week'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'status' => 'scheduled',
            'created_by' => $user->id,
            'deep_link' => $data['deep_link'] ?? null,
            'extra_data' => $data['extra_data'] ?? null,
        ]);

        $schedule = app(NotificationScheduleService::class);
        $broadcast->next_run_at = $schedule->initialNextRun($broadcast);
        $broadcast->save();

        if ($broadcast->next_run_at && $broadcast->next_run_at->lte(now())) {
            SendPushBroadcastJob::dispatch($broadcast->id);
        }

        return response()->json([
            'success' => true,
            'data' => $broadcast->fresh(),
        ], 201);
    }

    public function cancel(Request $request, int $id)
    {
        $user = $this->resolveAdminUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $b = NotificationBroadcast::query()->find($id);
        if (! $b) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        if (! in_array($b->status, ['scheduled', 'draft'], true)) {
            return response()->json(['success' => false, 'message' => 'Cannot cancel this broadcast'], 422);
        }

        $b->update([
            'status' => 'cancelled',
            'next_run_at' => null,
        ]);

        return response()->json(['success' => true, 'data' => $b->fresh()]);
    }

    public function sendNow(Request $request, int $id)
    {
        $user = $this->resolveAdminUser($request);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $b = NotificationBroadcast::query()->find($id);
        if (! $b) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        if ($b->status === 'cancelled') {
            return response()->json(['success' => false, 'message' => 'Broadcast cancelled'], 422);
        }

        if ($b->status === 'processing') {
            return response()->json(['success' => false, 'message' => 'Already processing'], 422);
        }

        if (! in_array($b->status, ['scheduled', 'completed', 'failed'], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid state for send-now'], 422);
        }

        $b->update([
            'status' => 'scheduled',
            'next_run_at' => now(),
        ]);
        SendPushBroadcastJob::dispatch($b->id);

        return response()->json(['success' => true, 'data' => $b->fresh()]);
    }
}

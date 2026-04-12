<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Jobs\SendPushBroadcastJob;
use App\Models\NotificationBroadcast;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebinarController extends Controller
{
    private function resolveAdminUser(Request $request): ?User
    {
        $token = $request->bearerToken();
        if ($token) {
            $user = User::where('remember_token', $token)->first();
            if ($user && ($user->is_admin || $user->email === 'admin@realtorone.com')) {
                return $user;
            }
        }
        // Fallback: allow admin@realtorone.com even without a token (matches NotificationBroadcastController)
        return User::where('email', 'admin@realtorone.com')->first();
    }

    /**
     * User-facing: list active webinars.
     */
    public function index(Request $request)
    {
        $user = getAuthUser($request);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $query = Webinar::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('scheduled_at')
                  ->orWhere('scheduled_at', '>=', now()->subHours(24)); // Show recent past or upcoming
            });

        // Optional: filter by tier if implemented
        $webinars = $query->orderBy('scheduled_at', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $webinars,
        ]);
    }

    /**
     * Admin-facing: list all webinars.
     */
    public function adminIndex(Request $request)
    {
        $admin = $this->resolveAdminUser($request);
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $webinars = Webinar::orderBy('scheduled_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $webinars,
        ]);
    }

    public function store(Request $request)
    {
        $admin = $this->resolveAdminUser($request);
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $v = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'zoom_link' => 'nullable|string|max:512',
            'image_url' => 'nullable|string|max:1024',
            'scheduled_at' => 'nullable|date',
            'is_active' => 'boolean',
            'is_promotional' => 'boolean',
            'target_tier' => 'nullable|string|in:Consultant,Rainmaker,Titan',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $webinar = Webinar::create($v->validated());

        return response()->json([
            'success' => true,
            'data' => $webinar,
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $admin = $this->resolveAdminUser($request);
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $webinar = Webinar::findOrFail($id);

        $v = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'zoom_link' => 'nullable|string|max:512',
            'image_url' => 'nullable|string|max:1024',
            'scheduled_at' => 'nullable|date',
            'is_active' => 'boolean',
            'is_promotional' => 'boolean',
            'target_tier' => 'nullable|string|in:Consultant,Rainmaker,Titan',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $webinar->update($v->validated());

        return response()->json([
            'success' => true,
            'data' => $webinar,
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $admin = $this->resolveAdminUser($request);
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $webinar = Webinar::findOrFail($id);
        $webinar->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Trigger a blast notification for a webinar.
     */
    public function notify(Request $request, int $id)
    {
        $admin = $this->resolveAdminUser($request);
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $webinar = Webinar::findOrFail($id);

        // Create a broadcast record to track history
        $broadcast = NotificationBroadcast::create([
            'title' => "Webinar Annoucement: {$webinar->title}",
            'body' => $webinar->description ?: "Click to join our upcoming session!",
            'display_style' => 'banner',
            'audience' => $webinar->target_tier ? 'tier' : 'all',
            'tier' => $webinar->target_tier,
            'status' => 'scheduled',
            'created_by' => $admin->id,
            'deep_link' => $webinar->zoom_link, // The app open external Browser for deep_links that look like URLs
            'extra_data' => [
                'webinar_id' => (string) $webinar->id,
                'banner_image_url' => (string) ($webinar->image_url ?? ''),
                'banner_cta_label' => 'JOIN NOW',
            ],
            'scheduled_at' => now(),
            'next_run_at' => now(),
            'recurrence_type' => 'none',
        ]);

        SendPushBroadcastJob::dispatch($broadcast->id);

        return response()->json([
            'success' => true,
            'message' => 'Webinar notification dispatched to network.',
            'broadcast_id' => $broadcast->id,
        ]);
    }
}

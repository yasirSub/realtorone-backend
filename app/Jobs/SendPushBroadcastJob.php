<?php

namespace App\Jobs;

use App\Models\NotificationBroadcast;
use App\Models\UserPushToken;
use App\Services\FcmSenderService;
use App\Services\NotificationRecipientResolver;
use App\Services\NotificationScheduleService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendPushBroadcastJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $broadcastId
    ) {}

    public function handle(
        FcmSenderService $fcm,
        NotificationRecipientResolver $resolver,
        NotificationScheduleService $scheduleService
    ): void {
        $broadcast = NotificationBroadcast::query()->find($this->broadcastId);
        if (! $broadcast) {
            return;
        }

        if (in_array($broadcast->status, ['cancelled'], true)) {
            return;
        }

        $claimed = NotificationBroadcast::query()
            ->whereKey($this->broadcastId)
            ->where('status', 'scheduled')
            ->update(['status' => 'processing', 'last_error' => null]);

        // Retries may pick up a row that is already in processing after a prior
        // attempt failed mid-run. Allow that state to continue instead of exiting,
        // otherwise the broadcast can get stuck in processing forever.
        if ($claimed === 0 && $broadcast->status !== 'processing') {
            return;
        }

        $broadcast->refresh();

        $userIds = $resolver->resolveUserIds($broadcast);
        $tokens = UserPushToken::query()
            ->whereIn('user_id', $userIds)
            ->get(['token', 'user_id']);
        $tokenCount = $tokens->count();

        $data = array_merge(
            [
                'display_style' => $broadcast->display_style,
                'broadcast_id' => (string) $broadcast->id,
                // Used by the mobile app to improve daily recurring UX (greeting).
                'recurrence_type' => (string) ($broadcast->recurrence_type ?? 'none'),
                'recurrence_time' => (string) ($broadcast->recurrence_time ?? ''),
                'timezone' => (string) ($broadcast->timezone ?? 'UTC'),
            ],
            $broadcast->deep_link ? ['deep_link' => $broadcast->deep_link] : [],
            is_array($broadcast->extra_data) ? array_map('strval', $broadcast->extra_data) : []
        );

        $sent = 0;
        if ($fcm->isConfigured()) {
            foreach ($tokens as $row) {
                if ($fcm->sendToToken($row->token, $broadcast->title, $broadcast->body, $data)) {
                    $sent++;
                }
            }
        } else {
            Log::info('SendPushBroadcastJob: FCM not configured, skipping device sends.', [
                'broadcast_id' => $broadcast->id,
                'recipient_count' => count($userIds),
                'token_rows' => $tokens->count(),
            ]);
        }

        $now = now();
        $broadcast->last_run_at = $now;
        $broadcast->last_sent_count = $sent;
        if (! $fcm->isConfigured()) {
            $broadcast->last_error = 'FCM not configured (set FIREBASE_PROJECT_ID and service account credentials).';
        } elseif ($tokenCount === 0) {
            $broadcast->last_error = 'No push tokens found for recipients.';
        } elseif ($sent === 0) {
            $broadcast->last_error = "Push send failed: 0/{$tokenCount} tokens delivered.";
            Log::error('SendPushBroadcastJob: zero tokens delivered.', [
                'broadcast_id' => $broadcast->id,
                'recipient_count' => count($userIds),
                'token_count' => $tokenCount,
            ]);
        }

        if ($sent === 0 && $tokenCount > 0) {
            $broadcast->status = 'failed';
            $broadcast->next_run_at = null;
        } elseif ($broadcast->recurrence_type !== 'none') {
            $broadcast->status = 'scheduled';
            $broadcast->next_run_at = $scheduleService->nextRunAfter($broadcast, $now);
        } else {
            $broadcast->status = 'completed';
            $broadcast->next_run_at = null;
        }

        $broadcast->save();
    }

    public function failed(?Throwable $exception): void
    {
        NotificationBroadcast::query()->whereKey($this->broadcastId)->update([
            'status' => 'failed',
            'last_error' => $exception?->getMessage() ?? 'Job failed',
        ]);
    }
}

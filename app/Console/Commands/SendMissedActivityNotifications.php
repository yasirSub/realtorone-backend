<?php

namespace App\Console\Commands;

use App\Models\NotificationAutomationDedupe;
use App\Models\User;
use App\Models\UserPushToken;
use App\Services\FcmSenderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendMissedActivityNotifications extends Command
{
    protected $signature = 'notifications:missed-activity';

    protected $description = 'Notify users who have no performance metric / activity logged today (deduped once per day)';

    public function handle(FcmSenderService $fcm): int
    {
        if (! $fcm->isConfigured()) {
            $this->warn('FCM not configured; skipping.');

            return self::SUCCESS;
        }

        $today = Carbon::today()->toDateString();
        $ruleKey = 'missed_activity';

        $userIdsWithActivityToday = DB::table('performance_metrics')
            ->whereDate('date', $today)
            ->pluck('user_id')
            ->unique()
            ->all();

        $candidates = User::query()
            ->where('email', '!=', 'admin@realtorone.com')
            ->whereNotIn('id', $userIdsWithActivityToday)
            ->pluck('id');

        $title = 'Keep your momentum';
        $body = 'You have not logged activity today. Open the app to stay on track.';

        $sent = 0;
        foreach ($candidates as $userId) {
            $exists = NotificationAutomationDedupe::query()
                ->where('user_id', $userId)
                ->where('rule_key', $ruleKey)
                ->whereDate('dedupe_date', $today)
                ->exists();

            if ($exists) {
                continue;
            }

            $tokens = UserPushToken::query()->where('user_id', $userId)->get();
            $sentThisUser = false;
            foreach ($tokens as $row) {
                if ($fcm->sendToToken(
                    $row->token,
                    $title,
                    $body,
                    [
                        'display_style' => 'standard',
                        'automation' => $ruleKey,
                    ]
                )) {
                    $sent++;
                    $sentThisUser = true;
                }
            }

            if ($sentThisUser) {
                NotificationAutomationDedupe::query()->create([
                    'user_id' => $userId,
                    'rule_key' => $ruleKey,
                    'dedupe_date' => $today,
                ]);
            }
        }

        $this->info("Missed-activity notifications sent: {$sent} token(s).");

        return self::SUCCESS;
    }
}

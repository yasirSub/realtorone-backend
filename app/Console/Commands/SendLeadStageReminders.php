<?php

namespace App\Console\Commands;

use App\Models\NotificationAutomationDedupe;
use App\Models\Result;
use App\Models\UserPushToken;
use App\Services\FcmSenderService;
use App\Support\CrmPipeline;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * When we send a CRM stage reminder (FCM):
 *
 * - Schedule: {@see \Illuminate\Console\Scheduling\Schedule} runs {@code notifications:lead-reminders} once per day (default 10:00).
 * - Eligible lead: {@code results} row {@code type = hot_lead}, has {@code lead_stage} in JSON notes, not {@code status = lost}.
 * - Skip if the lead is in a terminal CRM flow bucket (retargeting, stalled, cold-calling nutshell/nurture lists) — nothing useful to “do today”.
 * - Skip if the rep already completed today’s work for that stage: either (a) {@code notes.daily_actions[Y-m-d][action_key] = yes} on the hot_lead, or (b) a {@code revenue_action} result exists for this client+day+action_key.
 * - Dedupe: at most one reminder per hot_lead per user per calendar day ({@code notification_automation_dedupes}).
 *
 * This is separate from {@see SendMissedActivityNotifications} (no performance metric logged) and admin {@see \App\Jobs\SendPushBroadcastJob} broadcasts.
 */
class SendLeadStageReminders extends Command
{
    protected $signature = 'notifications:lead-reminders';

    protected $description = 'Daily FCM reminders: complete today’s pipeline action for active hot leads (deduped)';

    public function handle(FcmSenderService $fcm): int
    {
        if (! $fcm->isConfigured()) {
            $this->warn('FCM not configured; skipping.');

            return self::SUCCESS;
        }

        $today = Carbon::today()->toDateString();
        $ruleKey = 'lead_stage_reminder';

        $leads = Result::query()
            ->where('type', 'hot_lead')
            ->whereNotNull('notes')
            ->get();

        $notificationsSent = 0;
        $driver = DB::connection()->getDriverName();

        foreach ($leads as $lead) {
            if (($lead->status ?? 'active') === 'lost') {
                continue;
            }

            $notes = json_decode($lead->notes, true);
            if (! is_array($notes)) {
                continue;
            }
            $notes = CrmPipeline::normalizeNotesMeta($notes);
            if (empty($notes['lead_stage'])) {
                continue;
            }

            $currentStage = $notes['lead_stage'];
            if ($this->shouldSkipTerminalPipelineBucket($notes, $currentStage)) {
                continue;
            }

            $userId = (int) $lead->user_id;

            $actionKey = CrmPipeline::actionKeyFromLeadStage($currentStage);
            if ($actionKey === null) {
                $actionKey = CrmPipeline::normalizeActionKey(str_replace(' ', '_', $currentStage));
            } else {
                $actionKey = CrmPipeline::normalizeActionKey($actionKey);
            }

            if ($this->dailyActionMarkedYesToday($notes, $actionKey, $today)) {
                continue;
            }

            $loggedToday = Result::query()
                ->where('user_id', $userId)
                ->where('type', 'revenue_action')
                ->where('client_name', $lead->client_name)
                ->whereDate('date', $today)
                ->where(function ($q) use ($driver, $actionKey) {
                    if ($driver === 'mysql') {
                        $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(notes, '$.action_key')) = ?", [$actionKey])
                            ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(notes, '$.action_key')) = ?", ['site_visit']);
                    } else {
                        $q->whereRaw("json_extract(notes, '$.action_key') = ?", [$actionKey])
                            ->orWhereRaw("json_extract(notes, '$.action_key') = ?", ['site_visit']);
                    }
                })
                ->exists();

            if ($loggedToday) {
                continue;
            }

            $dedupeKey = $ruleKey.'_'.$lead->id;

            $exists = NotificationAutomationDedupe::query()
                ->where('user_id', $userId)
                ->where('rule_key', $dedupeKey)
                ->whereDate('dedupe_date', $today)
                ->exists();

            if ($exists) {
                continue;
            }

            $title = 'Daily CRM Reminder';
            $clientLabel = $lead->client_name ?: 'your client';
            $stageLabel = CrmPipeline::actionLabelForLeadStage($currentStage);
            $body = "Complete {$stageLabel} for {$clientLabel} today.";

            $tokens = UserPushToken::query()->where('user_id', $userId)->get();
            $sentThisLead = false;

            foreach ($tokens as $tokenRow) {
                if ($fcm->sendToToken($tokenRow->token, $title, $body, [
                    'display_style' => 'standard',
                    'automation' => 'crm_reminder',
                    'client_id' => (string) $lead->id,
                ])) {
                    $notificationsSent++;
                    $sentThisLead = true;
                }
            }

            if ($sentThisLead) {
                NotificationAutomationDedupe::query()->create([
                    'user_id' => $userId,
                    'rule_key' => $dedupeKey,
                    'dedupe_date' => $today,
                ]);
            }
        }

        $this->info("Lead stage reminders sent: {$notificationsSent} token(s).");

        return self::SUCCESS;
    }

    /**
     * True when hot_lead notes say today’s block is already done (Deal Room daily checklist).
     *
     * @param  array<string, mixed>  $notes
     */
    private function dailyActionMarkedYesToday(array $notes, string $actionKey, string $today): bool
    {
        $daily = $notes['daily_actions'] ?? null;
        if (! is_array($daily)) {
            return false;
        }
        $day = $daily[$today] ?? null;
        if (! is_array($day)) {
            return false;
        }
        if (($day[$actionKey] ?? null) === 'yes') {
            return true;
        }
        if ($actionKey === 'deal_negotiation' && ($day['site_visit'] ?? null) === 'yes') {
            return true;
        }

        return false;
    }

    /**
     * Do not nag when the flow is in a terminal / nurture state.
     *
     * @param  array<string, mixed>  $notes
     */
    private function shouldSkipTerminalPipelineBucket(array $notes, string $normalizedStage): bool
    {
        $s = strtolower(trim($normalizedStage));

        return match ($s) {
            'cold calling' => $this->bucketIn($notes['cold_calling'] ?? null, [
                'retargeting', 'nutshell', 'nurture_whatsapp',
            ]),
            'follow up back' => $this->bucketIn($notes['follow_up'] ?? null, ['retargeting', 'stalled']),
            'client meeting' => $this->bucketIn($notes['client_meeting'] ?? null, ['retargeting', 'stalled']),
            'deal negotiation' => $this->bucketIn($notes['deal_negotiation'] ?? null, ['retargeting', 'stalled']),
            'deal close' => $this->bucketIn($notes['deal_closure'] ?? null, ['retargeting', 'stalled']),
            default => false,
        };
    }

    /**
     * @param  mixed  $block
     * @param  array<int, string>  $terminal
     */
    private function bucketIn($block, array $terminal): bool
    {
        if (! is_array($block)) {
            return false;
        }
        $b = strtolower((string) ($block['bucket'] ?? 'in_progress'));

        return in_array($b, $terminal, true);
    }
}

<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Cold-calling flowchart: CALL / WhatsApp attempts, outcomes, lists (Nutshell, Retargeting, Nurture).
 *
 * State lives in hot_lead notes under key `cold_calling`.
 */
class ColdCallingFlow
{
    public const BUCKET_IN_PROGRESS = 'in_progress';

    public const BUCKET_RETARGETING = 'retargeting';

    public const BUCKET_NUTSHELL = 'nutshell';

    public const BUCKET_NURTURE_WHATSAPP = 'nurture_whatsapp';

    public const MAX_CALL_ATTEMPTS = 4;

    public const MAX_WA_ATTEMPTS = 3;

    /**
     * @return array<string, mixed>
     */
    public static function defaultState(): array
    {
        return [
            'mode' => null,
            'call_attempt' => 0,
            'wa_attempt' => 0,
            'bucket' => self::BUCKET_IN_PROGRESS,
            'next_contact_at' => null,
            'last_touch_at' => null,
            'last_result' => null,
            'touch_log' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $meta  Full hot_lead notes array (will be merged).
     * @param  array<string, mixed>  $input mode, result, schedule, next_contact_date
     * @return array{meta: array<string, mixed>, advanced_to_follow_up: bool, message: string}
     */
    public static function applyTouch(array $meta, array $input): array
    {
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'cold calling') {
            throw new \InvalidArgumentException('Lead is not in cold calling stage.');
        }

        $cc = array_merge(self::defaultState(), is_array($meta['cold_calling'] ?? null) ? $meta['cold_calling'] : []);
        if (in_array($cc['bucket'] ?? '', [self::BUCKET_NUTSHELL, self::BUCKET_NURTURE_WHATSAPP], true)) {
            throw new \InvalidArgumentException('This lead is in a terminal cold-call bucket. Move or reopen from admin.');
        }

        $mode = strtolower((string) ($input['mode'] ?? 'call'));
        if (! in_array($mode, ['call', 'whatsapp'], true)) {
            throw new \InvalidArgumentException('mode must be call or whatsapp.');
        }

        $result = strtolower((string) ($input['result'] ?? ''));
        $allowed = ['interested', 'exploring', 'not_interested', 'no_answer', 'no_reply'];
        if (! in_array($result, $allowed, true)) {
            throw new \InvalidArgumentException('result must be one of: '.implode(', ', $allowed));
        }

        $cc['mode'] = $mode;
        $now = now();
        $cc['last_touch_at'] = $now->toIso8601String();
        $cc['last_result'] = $result;

        $logEntry = [
            'at' => $cc['last_touch_at'],
            'mode' => $mode,
            'result' => $result,
        ];
        $cc['touch_log'] = is_array($cc['touch_log'] ?? null) ? $cc['touch_log'] : [];
        $cc['touch_log'][] = $logEntry;

        $advanced = false;
        $message = '';

        if (in_array($result, ['interested', 'exploring'], true)) {
            $meta['lead_stage'] = 'follow up back';
            $cc['bucket'] = self::BUCKET_IN_PROGRESS;
            $cc['next_contact_at'] = null;
            $advanced = true;
            $message = 'Lead moved to Follow-up stage.';

            return ['meta' => array_merge($meta, ['cold_calling' => $cc]), 'advanced_to_follow_up' => true, 'message' => $message];
        }

        if ($result === 'not_interested') {
            $cc['bucket'] = self::BUCKET_RETARGETING;
            $meta['lead_package'] = $meta['lead_package'] ?? 'nurture';
            $message = 'Lead marked for Retargeting list.';

            return ['meta' => array_merge($meta, ['cold_calling' => $cc]), 'advanced_to_follow_up' => false, 'message' => $message];
        }

        // no_answer (call) or no_reply (whatsapp)
        if ($mode === 'call' && $result === 'no_answer') {
            $cc['call_attempt'] = (int) ($cc['call_attempt'] ?? 0) + 1;
            if ($cc['call_attempt'] >= self::MAX_CALL_ATTEMPTS) {
                $cc['bucket'] = self::BUCKET_NUTSHELL;
                $cc['next_contact_at'] = null;
                $message = 'Max call attempts reached — moved to Nutshell list (no answer).';

                return ['meta' => array_merge($meta, ['cold_calling' => $cc]), 'advanced_to_follow_up' => false, 'message' => $message];
            }
            $cc['next_contact_at'] = self::resolveNextContact($input, $now);
            $message = 'Call attempt '.$cc['call_attempt'].' of '.self::MAX_CALL_ATTEMPTS.'. Next contact scheduled.';

            return ['meta' => array_merge($meta, ['cold_calling' => $cc]), 'advanced_to_follow_up' => false, 'message' => $message];
        }

        if ($mode === 'whatsapp' && $result === 'no_reply') {
            $cc['wa_attempt'] = (int) ($cc['wa_attempt'] ?? 0) + 1;
            if ($cc['wa_attempt'] >= self::MAX_WA_ATTEMPTS) {
                $cc['bucket'] = self::BUCKET_NURTURE_WHATSAPP;
                $cc['next_contact_at'] = null;
                $message = 'Max WhatsApp attempts — moved to Nurture (Retargeting) list.';

                return ['meta' => array_merge($meta, ['cold_calling' => $cc]), 'advanced_to_follow_up' => false, 'message' => $message];
            }
            $cc['next_contact_at'] = self::resolveNextContact($input, $now);
            $message = 'WhatsApp attempt '.$cc['wa_attempt'].' of '.self::MAX_WA_ATTEMPTS.'. Next contact scheduled.';

            return ['meta' => array_merge($meta, ['cold_calling' => $cc]), 'advanced_to_follow_up' => false, 'message' => $message];
        }

        throw new \InvalidArgumentException('Invalid mode/result combination.');
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function resolveNextContact(array $input, Carbon $now): string
    {
        $schedule = strtolower((string) ($input['schedule'] ?? 'tomorrow'));
        $custom = $input['next_contact_date'] ?? null;

        if ($schedule === 'custom' && is_string($custom) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $custom)) {
            return Carbon::parse($custom)->startOfDay()->toIso8601String();
        }
        if ($schedule === 'plus_2_days') {
            return $now->copy()->addDays(2)->startOfDay()->toIso8601String();
        }

        return $now->copy()->addDay()->startOfDay()->toIso8601String();
    }

    /**
     * Whether this lead should appear in "today's cold calling" list.
     *
     * @param  array<string, mixed>  $meta
     */
    public static function isDueForColdCallingToday(array $meta, ?Carbon $today = null): bool
    {
        $today = $today ?? Carbon::today();
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'cold calling') {
            return false;
        }

        $cc = array_merge(self::defaultState(), is_array($meta['cold_calling'] ?? null) ? $meta['cold_calling'] : []);
        $bucket = $cc['bucket'] ?? self::BUCKET_IN_PROGRESS;
        if (in_array($bucket, [self::BUCKET_NUTSHELL, self::BUCKET_NURTURE_WHATSAPP], true)) {
            return false;
        }

        $next = $cc['next_contact_at'] ?? null;
        if ($next === null || $next === '') {
            return true;
        }

        try {
            $d = Carbon::parse($next)->startOfDay();

            return $d->lte($today);
        } catch (\Throwable) {
            return true;
        }
    }
}

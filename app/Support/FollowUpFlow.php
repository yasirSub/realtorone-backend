<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Follow-up stage (PDF CRM stage 2). PDF page has a heading only; this implements a practical structure:
 * Touchpoint → outcome → schedule next follow-up, or advance to Client Meeting, or retarget / stall.
 *
 * State lives in hot_lead notes under key `follow_up`.
 */
class FollowUpFlow
{
    public const BUCKET_IN_PROGRESS = 'in_progress';

    public const BUCKET_RETARGETING = 'retargeting';

    public const BUCKET_STALLED = 'stalled';

    /** Max scheduled follow-up touches before "stalled" nurture bucket */
    public const MAX_CONTINUE_TOUCHES = 5;

    /**
     * @return array<string, mixed>
     */
    public static function defaultState(): array
    {
        return [
            'mode' => null,
            'touch_count' => 0,
            'bucket' => self::BUCKET_IN_PROGRESS,
            'next_contact_at' => null,
            'last_touch_at' => null,
            'last_result' => null,
            'touch_log' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @param  array<string, mixed>  $input  mode, result, schedule, next_contact_date
     * @return array{meta: array<string, mixed>, advanced_to_client_meeting: bool, message: string}
     */
    public static function applyTouch(array $meta, array $input): array
    {
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'follow up back') {
            throw new \InvalidArgumentException('Lead is not in Follow-up stage.');
        }

        $fu = array_merge(self::defaultState(), is_array($meta['follow_up'] ?? null) ? $meta['follow_up'] : []);
        if (in_array($fu['bucket'] ?? '', [self::BUCKET_STALLED], true)) {
            throw new \InvalidArgumentException('This lead is in a stalled follow-up state. Update stage manually or clear bucket in notes.');
        }

        $mode = strtolower((string) ($input['mode'] ?? 'call'));
        if (! in_array($mode, ['call', 'whatsapp', 'email'], true)) {
            throw new \InvalidArgumentException('mode must be call, whatsapp, or email.');
        }

        $result = strtolower((string) ($input['result'] ?? ''));
        $allowed = ['ready_for_meeting', 'not_interested', 'continue_touch'];
        if (! in_array($result, $allowed, true)) {
            throw new \InvalidArgumentException('result must be one of: '.implode(', ', $allowed));
        }

        $fu['mode'] = $mode;
        $now = now();
        $fu['last_touch_at'] = $now->toIso8601String();
        $fu['last_result'] = $result;
        $fu['touch_log'] = is_array($fu['touch_log'] ?? null) ? $fu['touch_log'] : [];
        $fu['touch_log'][] = [
            'at' => $fu['last_touch_at'],
            'mode' => $mode,
            'result' => $result,
        ];

        if ($result === 'ready_for_meeting') {
            $meta['lead_stage'] = 'client meeting';
            $fu['bucket'] = self::BUCKET_IN_PROGRESS;
            $fu['next_contact_at'] = null;

            return [
                'meta' => array_merge($meta, ['follow_up' => $fu]),
                'advanced_to_client_meeting' => true,
                'message' => 'Lead moved to Client Meeting stage.',
            ];
        }

        if ($result === 'not_interested') {
            $fu['bucket'] = self::BUCKET_RETARGETING;
            $meta['lead_package'] = $meta['lead_package'] ?? 'nurture';

            return [
                'meta' => array_merge($meta, ['follow_up' => $fu]),
                'advanced_to_client_meeting' => false,
                'message' => 'Lead marked not interested — Retargeting list.',
            ];
        }

        // continue_touch — schedule another follow-up; stall after max touches
        $fu['touch_count'] = (int) ($fu['touch_count'] ?? 0) + 1;
        if ($fu['touch_count'] >= self::MAX_CONTINUE_TOUCHES) {
            $fu['bucket'] = self::BUCKET_STALLED;
            $fu['next_contact_at'] = null;

            return [
                'meta' => array_merge($meta, ['follow_up' => $fu]),
                'advanced_to_client_meeting' => false,
                'message' => 'Max follow-up touches reached — marked Stalled (nurture / revisit later).',
            ];
        }

        $fu['next_contact_at'] = ColdCallingFlow::resolveNextContact($input, $now);

        return [
            'meta' => array_merge($meta, ['follow_up' => $fu]),
            'advanced_to_client_meeting' => false,
            'message' => 'Follow-up touch '.$fu['touch_count'].' of '.self::MAX_CONTINUE_TOUCHES.'. Next contact scheduled.',
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function isDueForFollowUpToday(array $meta, ?Carbon $today = null): bool
    {
        $today = $today ?? Carbon::today();
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'follow up back') {
            return false;
        }

        $fu = array_merge(self::defaultState(), is_array($meta['follow_up'] ?? null) ? $meta['follow_up'] : []);
        $bucket = $fu['bucket'] ?? self::BUCKET_IN_PROGRESS;
        if (in_array($bucket, [self::BUCKET_STALLED, self::BUCKET_RETARGETING], true)) {
            return false;
        }

        $next = $fu['next_contact_at'] ?? null;
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

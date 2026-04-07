<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Client Meeting stage (CRM 3). State in hot_lead notes under `client_meeting`.
 */
class ClientMeetingFlow
{
    public const BUCKET_IN_PROGRESS = 'in_progress';

    public const BUCKET_RETARGETING = 'retargeting';

    public const BUCKET_STALLED = 'stalled';

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
     * @return array{meta: array<string, mixed>, advanced_to_deal_negotiation: bool, message: string}
     */
    public static function applyTouch(array $meta, array $input): array
    {
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'client meeting') {
            throw new \InvalidArgumentException('Lead is not in Client Meeting stage.');
        }

        $cm = array_merge(self::defaultState(), is_array($meta['client_meeting'] ?? null) ? $meta['client_meeting'] : []);
        if (in_array($cm['bucket'] ?? '', [self::BUCKET_STALLED], true)) {
            throw new \InvalidArgumentException('This lead is stalled at client meeting. Update stage or notes manually.');
        }

        $mode = strtolower((string) ($input['mode'] ?? 'in_person'));
        if (! in_array($mode, ['in_person', 'video', 'call', 'whatsapp'], true)) {
            throw new \InvalidArgumentException('mode must be in_person, video, call, or whatsapp.');
        }

        $result = strtolower((string) ($input['result'] ?? ''));
        $allowed = ['advance_to_negotiation', 'not_interested', 'continue_touch'];
        if (! in_array($result, $allowed, true)) {
            throw new \InvalidArgumentException('result must be one of: '.implode(', ', $allowed));
        }

        $cm['mode'] = $mode;
        $now = now();
        $cm['last_touch_at'] = $now->toIso8601String();
        $cm['last_result'] = $result;
        $cm['touch_log'] = is_array($cm['touch_log'] ?? null) ? $cm['touch_log'] : [];
        $cm['touch_log'][] = [
            'at' => $cm['last_touch_at'],
            'mode' => $mode,
            'result' => $result,
        ];

        if ($result === 'advance_to_negotiation') {
            $meta['lead_stage'] = 'deal negotiation';
            $cm['bucket'] = self::BUCKET_IN_PROGRESS;
            $cm['next_contact_at'] = null;

            return [
                'meta' => array_merge($meta, ['client_meeting' => $cm]),
                'advanced_to_deal_negotiation' => true,
                'message' => 'Lead moved to Deal Negotiation stage.',
            ];
        }

        if ($result === 'not_interested') {
            $cm['bucket'] = self::BUCKET_RETARGETING;
            $meta['lead_package'] = $meta['lead_package'] ?? 'nurture';

            return [
                'meta' => array_merge($meta, ['client_meeting' => $cm]),
                'advanced_to_deal_negotiation' => false,
                'message' => 'Marked not interested — Retargeting list.',
            ];
        }

        $cm['touch_count'] = (int) ($cm['touch_count'] ?? 0) + 1;
        if ($cm['touch_count'] >= self::MAX_CONTINUE_TOUCHES) {
            $cm['bucket'] = self::BUCKET_STALLED;
            $cm['next_contact_at'] = null;

            return [
                'meta' => array_merge($meta, ['client_meeting' => $cm]),
                'advanced_to_deal_negotiation' => false,
                'message' => 'Max meeting follow-ups reached — stalled (revisit or advance manually).',
            ];
        }

        $cm['next_contact_at'] = ColdCallingFlow::resolveNextContact($input, $now);

        return [
            'meta' => array_merge($meta, ['client_meeting' => $cm]),
            'advanced_to_deal_negotiation' => false,
            'message' => 'Meeting touch '.$cm['touch_count'].' of '.self::MAX_CONTINUE_TOUCHES.'. Next step scheduled.',
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function isDueForClientMeetingToday(array $meta, ?Carbon $today = null): bool
    {
        $today = $today ?? Carbon::today();
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'client meeting') {
            return false;
        }

        $cm = array_merge(self::defaultState(), is_array($meta['client_meeting'] ?? null) ? $meta['client_meeting'] : []);
        $bucket = $cm['bucket'] ?? self::BUCKET_IN_PROGRESS;
        if (in_array($bucket, [self::BUCKET_STALLED, self::BUCKET_RETARGETING], true)) {
            return false;
        }

        $next = $cm['next_contact_at'] ?? null;
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

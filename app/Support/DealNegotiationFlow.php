<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Deal Negotiation stage (CRM 4). State in hot_lead notes under `deal_negotiation`.
 */
class DealNegotiationFlow
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
     * @param  array<string, mixed>  $input
     * @return array{meta: array<string, mixed>, advanced_to_deal_close: bool, message: string}
     */
    public static function applyTouch(array $meta, array $input): array
    {
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'deal negotiation') {
            throw new \InvalidArgumentException('Lead is not in Deal Negotiation stage.');
        }

        $dn = array_merge(self::defaultState(), is_array($meta['deal_negotiation'] ?? null) ? $meta['deal_negotiation'] : []);
        if (in_array($dn['bucket'] ?? '', [self::BUCKET_STALLED], true)) {
            throw new \InvalidArgumentException('This lead is stalled at negotiation. Update stage or notes manually.');
        }

        $mode = strtolower((string) ($input['mode'] ?? 'call'));
        if (! in_array($mode, ['in_person', 'video', 'call', 'whatsapp', 'email'], true)) {
            throw new \InvalidArgumentException('mode must be in_person, video, call, whatsapp, or email.');
        }

        $result = strtolower((string) ($input['result'] ?? ''));
        $allowed = ['advance_to_closure', 'not_interested', 'continue_touch'];
        if (! in_array($result, $allowed, true)) {
            throw new \InvalidArgumentException('result must be one of: '.implode(', ', $allowed));
        }

        $dn['mode'] = $mode;
        $now = now();
        $dn['last_touch_at'] = $now->toIso8601String();
        $dn['last_result'] = $result;
        $dn['touch_log'] = is_array($dn['touch_log'] ?? null) ? $dn['touch_log'] : [];
        $dn['touch_log'][] = [
            'at' => $dn['last_touch_at'],
            'mode' => $mode,
            'result' => $result,
        ];

        if ($result === 'advance_to_closure') {
            $meta['lead_stage'] = 'deal close';
            $dn['bucket'] = self::BUCKET_IN_PROGRESS;
            $dn['next_contact_at'] = null;

            return [
                'meta' => array_merge($meta, ['deal_negotiation' => $dn]),
                'advanced_to_deal_close' => true,
                'message' => 'Lead moved to Deal Closure stage.',
            ];
        }

        if ($result === 'not_interested') {
            $dn['bucket'] = self::BUCKET_RETARGETING;
            $meta['lead_package'] = $meta['lead_package'] ?? 'nurture';

            return [
                'meta' => array_merge($meta, ['deal_negotiation' => $dn]),
                'advanced_to_deal_close' => false,
                'message' => 'Marked not interested — Retargeting list.',
            ];
        }

        $dn['touch_count'] = (int) ($dn['touch_count'] ?? 0) + 1;
        if ($dn['touch_count'] >= self::MAX_CONTINUE_TOUCHES) {
            $dn['bucket'] = self::BUCKET_STALLED;
            $dn['next_contact_at'] = null;

            return [
                'meta' => array_merge($meta, ['deal_negotiation' => $dn]),
                'advanced_to_deal_close' => false,
                'message' => 'Max negotiation follow-ups reached — stalled.',
            ];
        }

        $dn['next_contact_at'] = ColdCallingFlow::resolveNextContact($input, $now);

        return [
            'meta' => array_merge($meta, ['deal_negotiation' => $dn]),
            'advanced_to_deal_close' => false,
            'message' => 'Negotiation touch '.$dn['touch_count'].' of '.self::MAX_CONTINUE_TOUCHES.'. Next contact scheduled.',
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function isDueForNegotiationToday(array $meta, ?Carbon $today = null): bool
    {
        $today = $today ?? Carbon::today();
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'deal negotiation') {
            return false;
        }

        $dn = array_merge(self::defaultState(), is_array($meta['deal_negotiation'] ?? null) ? $meta['deal_negotiation'] : []);
        $bucket = $dn['bucket'] ?? self::BUCKET_IN_PROGRESS;
        if (in_array($bucket, [self::BUCKET_STALLED, self::BUCKET_RETARGETING], true)) {
            return false;
        }

        $next = $dn['next_contact_at'] ?? null;
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

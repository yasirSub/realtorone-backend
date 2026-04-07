<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Deal Closure stage (CRM 5 — final pipeline stage before recording a won deal).
 * State in hot_lead notes under `deal_closure`. Use continue_touch for paperwork follow-ups; lost for pipeline exit.
 */
class DealClosureFlow
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
     * @return array{meta: array<string, mixed>, message: string}
     */
    public static function applyTouch(array $meta, array $input): array
    {
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'deal close') {
            throw new \InvalidArgumentException('Lead is not in Deal Closure stage.');
        }

        $dc = array_merge(self::defaultState(), is_array($meta['deal_closure'] ?? null) ? $meta['deal_closure'] : []);
        if (in_array($dc['bucket'] ?? '', [self::BUCKET_STALLED], true)) {
            throw new \InvalidArgumentException('This deal closure is stalled. Update notes or status manually.');
        }

        $mode = strtolower((string) ($input['mode'] ?? 'call'));
        if (! in_array($mode, ['call', 'whatsapp', 'email'], true)) {
            throw new \InvalidArgumentException('mode must be call, whatsapp, or email.');
        }

        $result = strtolower((string) ($input['result'] ?? ''));
        $allowed = ['lost', 'continue_touch'];
        if (! in_array($result, $allowed, true)) {
            throw new \InvalidArgumentException('result must be one of: '.implode(', ', $allowed));
        }

        $dc['mode'] = $mode;
        $now = now();
        $dc['last_touch_at'] = $now->toIso8601String();
        $dc['last_result'] = $result;
        $dc['touch_log'] = is_array($dc['touch_log'] ?? null) ? $dc['touch_log'] : [];
        $dc['touch_log'][] = [
            'at' => $dc['last_touch_at'],
            'mode' => $mode,
            'result' => $result,
        ];

        if ($result === 'lost') {
            $dc['bucket'] = self::BUCKET_RETARGETING;
            $meta['lead_package'] = $meta['lead_package'] ?? 'nurture';

            return [
                'meta' => array_merge($meta, ['deal_closure' => $dc]),
                'message' => 'Deal marked lost at closure — Retargeting list.',
            ];
        }

        $dc['touch_count'] = (int) ($dc['touch_count'] ?? 0) + 1;
        if ($dc['touch_count'] >= self::MAX_CONTINUE_TOUCHES) {
            $dc['bucket'] = self::BUCKET_STALLED;
            $dc['next_contact_at'] = null;

            return [
                'meta' => array_merge($meta, ['deal_closure' => $dc]),
                'message' => 'Max closure follow-ups reached — stalled (paperwork / revisit).',
            ];
        }

        $dc['next_contact_at'] = ColdCallingFlow::resolveNextContact($input, $now);

        return [
            'meta' => array_merge($meta, ['deal_closure' => $dc]),
            'message' => 'Closure follow-up '.$dc['touch_count'].' of '.self::MAX_CONTINUE_TOUCHES.'. Next touch scheduled.',
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public static function isDueForDealClosureToday(array $meta, ?Carbon $today = null): bool
    {
        $today = $today ?? Carbon::today();
        $stage = strtolower(trim((string) ($meta['lead_stage'] ?? '')));
        if ($stage !== 'deal close') {
            return false;
        }

        $dc = array_merge(self::defaultState(), is_array($meta['deal_closure'] ?? null) ? $meta['deal_closure'] : []);
        $bucket = $dc['bucket'] ?? self::BUCKET_IN_PROGRESS;
        if (in_array($bucket, [self::BUCKET_STALLED, self::BUCKET_RETARGETING], true)) {
            return false;
        }

        $next = $dc['next_contact_at'] ?? null;
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

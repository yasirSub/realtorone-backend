<?php

namespace App\Support;

/**
 * Canonical Deal Room CRM pipeline (Mobile Screen Content.pdf).
 */
class CrmPipeline
{
    public const ACTION_CONFIG = [
        ['key' => 'cold_calling', 'label' => 'Cold Calling'],
        ['key' => 'follow_up_back', 'label' => 'Follow-up'],
        ['key' => 'client_meeting', 'label' => 'Client Meeting'],
        ['key' => 'deal_negotiation', 'label' => 'Deal Negotiation'],
        ['key' => 'deal_close', 'label' => 'Deal Closure'],
    ];

    public const ACTION_LABELS = [
        'cold_calling' => 'Cold Calling',
        'follow_up_back' => 'Follow-up',
        'client_meeting' => 'Client Meeting',
        'deal_negotiation' => 'Deal Negotiation',
        'deal_close' => 'Deal Closure',
        // Legacy keys (still accepted on write)
        'site_visit' => 'Deal Negotiation',
    ];

    /** Ordered human-readable lead_stage values after auto-advance */
    public const ORDERED_STAGE_STRINGS = [
        'cold calling',
        'follow up back',
        'client meeting',
        'deal negotiation',
        'deal close',
    ];

    public static function normalizeActionKey(string $key): string
    {
        $k = strtolower(trim($key));

        return match ($k) {
            'site_visit', 'site_visite' => 'deal_negotiation',
            default => $k,
        };
    }

    public static function humanStageFromActionKey(string $actionKey): string
    {
        $k = self::normalizeActionKey($actionKey);

        return match ($k) {
            'cold_calling' => 'cold calling',
            'follow_up_back' => 'follow up back',
            'client_meeting' => 'client meeting',
            'deal_negotiation' => 'deal negotiation',
            'deal_close' => 'deal close',
            default => str_replace('_', ' ', $k),
        };
    }

    public static function normalizeLeadStageString(?string $stage): ?string
    {
        if ($stage === null || $stage === '') {
            return null;
        }
        $s = strtolower(trim($stage));
        $map = [
            'site visite' => 'deal negotiation',
            'site visit' => 'deal negotiation',
            'site_visit' => 'deal negotiation',
            'clint meeting' => 'client meeting',
        ];

        return $map[$s] ?? $s;
    }

    /**
     * Map normalized lead_stage string to the key used in daily_actions and revenue_action notes.
     */
    public static function actionKeyFromLeadStage(string $normalizedStage): ?string
    {
        $s = strtolower(trim($normalizedStage));

        return match ($s) {
            'cold calling' => 'cold_calling',
            'follow up back' => 'follow_up_back',
            'client meeting' => 'client_meeting',
            'deal negotiation' => 'deal_negotiation',
            'deal close' => 'deal_close',
            default => null,
        };
    }

    public static function actionLabelForLeadStage(string $normalizedStage): string
    {
        $key = self::actionKeyFromLeadStage($normalizedStage);

        return $key !== null
            ? (self::ACTION_LABELS[$key] ?? ucwords($normalizedStage))
            : ucwords($normalizedStage);
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    public static function normalizeNotesMeta(array $meta): array
    {
        if (isset($meta['lead_stage']) && is_string($meta['lead_stage'])) {
            $n = self::normalizeLeadStageString($meta['lead_stage']);
            if ($n !== null) {
                $meta['lead_stage'] = $n;
            }
        }

        if (isset($meta['daily_actions']) && is_array($meta['daily_actions'])) {
            foreach ($meta['daily_actions'] as $date => $actions) {
                if (! is_array($actions)) {
                    continue;
                }
                if (isset($actions['site_visit']) && ! isset($actions['deal_negotiation'])) {
                    $actions['deal_negotiation'] = $actions['site_visit'];
                }
                unset($actions['site_visit']);
                $meta['daily_actions'][$date] = $actions;
            }
        }

        return $meta;
    }
}

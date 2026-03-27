<?php

namespace App\Services;

use App\Models\NotificationBroadcast;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationRecipientResolver
{
    /**
     * @return list<int>
     */
    public function resolveUserIds(NotificationBroadcast $broadcast): array
    {
        $audience = $broadcast->audience;

        if ($audience === 'all') {
            return User::query()
                ->where('email', '!=', 'admin@realtorone.com')
                ->pluck('id')
                ->all();
        }

        if ($audience === 'tier') {
            $tier = $broadcast->tier;
            if (! is_string($tier) || $tier === '') {
                return [];
            }

            return User::query()
                ->where('membership_tier', $tier)
                ->where('email', '!=', 'admin@realtorone.com')
                ->pluck('id')
                ->all();
        }

        if ($audience === 'users') {
            $ids = $broadcast->target_user_ids;

            return Collection::make(is_array($ids) ? $ids : [])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
        }

        return [];
    }
}

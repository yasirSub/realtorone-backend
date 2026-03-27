<?php

namespace App\Services;

use App\Models\NotificationBroadcast;
use Carbon\Carbon;

class NotificationScheduleService
{
    public function initialNextRun(NotificationBroadcast $broadcast): Carbon
    {
        $tz = $broadcast->timezone ?: config('app.timezone', 'UTC');

        if ($broadcast->recurrence_type === 'none') {
            if ($broadcast->scheduled_at) {
                return $broadcast->scheduled_at->copy()->utc();
            }

            return now()->utc();
        }

        return $this->nextRecurringSlot(
            $tz,
            $broadcast->recurrence_type,
            $broadcast->recurrence_time ?: '09:00',
            $broadcast->recurrence_day_of_week,
            $broadcast->scheduled_at,
            now()->utc()
        );
    }

    public function nextRunAfter(NotificationBroadcast $broadcast, \DateTimeInterface $afterUtc): ?Carbon
    {
        if ($broadcast->recurrence_type === 'none') {
            return null;
        }

        $tz = $broadcast->timezone ?: config('app.timezone', 'UTC');

        return $this->nextRecurringSlot(
            $tz,
            $broadcast->recurrence_type,
            $broadcast->recurrence_time ?: '09:00',
            $broadcast->recurrence_day_of_week,
            null,
            Carbon::instance($afterUtc)->utc()->addSecond()
        );
    }

    private function nextRecurringSlot(
        string $tz,
        string $recurrenceType,
        string $timeStr,
        ?int $dayOfWeek,
        ?Carbon $anchorDate,
        Carbon $notBeforeUtc
    ): Carbon {
        $notBefore = $notBeforeUtc->copy()->timezone($tz);
        [$h, $m] = array_map('intval', array_pad(explode(':', $timeStr, 2), 2, 0));

        if ($recurrenceType === 'daily') {
            $candidate = $notBefore->copy()->setTime($h, $m, 0);
            if ($candidate->lessThanOrEqualTo($notBefore)) {
                $candidate->addDay();
            }

            return $candidate->utc();
        }

        if ($recurrenceType === 'weekly') {
            $dow = $dayOfWeek !== null ? (int) $dayOfWeek : (int) $notBefore->dayOfWeek;
            $start = $anchorDate
                ? $anchorDate->copy()->timezone($tz)->startOfDay()
                : $notBefore->copy()->startOfDay();
            $candidate = $start->setTime($h, $m, 0);

            for ($i = 0; $i < 14; $i++) {
                if ((int) $candidate->dayOfWeek === $dow && $candidate->greaterThan($notBefore)) {
                    return $candidate->utc();
                }
                $candidate->addDay();
            }

            return $notBefore->copy()->addWeek()->setTime($h, $m, 0)->utc();
        }

        return $notBeforeUtc->copy()->utc();
    }
}

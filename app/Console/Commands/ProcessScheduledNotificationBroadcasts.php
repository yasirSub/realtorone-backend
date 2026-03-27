<?php

namespace App\Console\Commands;

use App\Jobs\SendPushBroadcastJob;
use App\Models\NotificationBroadcast;
use Illuminate\Console\Command;

class ProcessScheduledNotificationBroadcasts extends Command
{
    protected $signature = 'notifications:process-scheduled';

    protected $description = 'Dispatch push notification broadcasts that are due (next_run_at <= now)';

    public function handle(): int
    {
        $ids = NotificationBroadcast::query()
            ->where('status', 'scheduled')
            ->where('next_run_at', '<=', now())
            ->orderBy('next_run_at')
            ->pluck('id');

        foreach ($ids as $id) {
            SendPushBroadcastJob::dispatch((int) $id);
        }

        if ($ids->isNotEmpty()) {
            $this->info('Dispatched '.$ids->count().' broadcast job(s).');
        }

        return self::SUCCESS;
    }
}

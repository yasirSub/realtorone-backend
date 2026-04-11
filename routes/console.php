<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('notifications:process-scheduled')->everyMinute();
Schedule::command('notifications:missed-activity')->dailyAt('09:00');
Schedule::command('notifications:lead-reminders')->dailyAt('10:00');
Schedule::command('app:send-daily-task-reminders')->everyMinute();
Schedule::command('leads:sync-excel')->everyMinute();

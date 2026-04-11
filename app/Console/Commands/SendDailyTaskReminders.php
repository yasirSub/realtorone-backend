<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\NotificationAutomationDedupe;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Models\UserPushToken;
use App\Services\FcmSenderService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendDailyTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-task-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send push notifications to users who have not completed their mandatory daily tasks/audio';

    /**
     * Execute the console command.
     */
    public function handle(FcmSenderService $fcm)
    {
        $this->info('Starting daily task reminders check...');

        // 1. Fetch the Control System Setting
        $setting = NotificationSetting::where('key', 'task_reminder')->first();
        if ($setting && !$setting->is_enabled) {
            $this->info('Task reminders are disabled globally via Notification Settings Control System. Exiting.');
            return 0;
        }

        $ruleKey = 'momentum_daily_reminders';

        $users = User::where('is_premium', true)
            ->where('status', '!=', 'inactive')
            ->where('email', '!=', 'admin@realtorone.com')
            ->get();

        $remindersSent = 0;

        foreach ($users as $user) {
            $day = (int) ($user->program_current_day ?? 1);
            
            // Check if control system changed the time delay constraint
            $timeOffset = 0; // minutes
            if ($setting && isset($setting->trigger_settings['delay_minutes'])) {
                $timeOffset = (int) $setting->trigger_settings['delay_minutes'];
            }

            $userTimezone = $user->timezone ?? 'UTC';
            $nowLocal = now($userTimezone);
            $todayLocal = $nowLocal->toDateString(); // User's local date for logic
            $currentMinutes = (int) $nowLocal->format('G') * 60 + (int) $nowLocal->format('i');

            foreach (['morning', 'evening'] as $period) {
                $ruleKey = "momentum_daily_reminders_{$period}";

                $alreadySent = NotificationAutomationDedupe::query()
                    ->where('user_id', $user->id)
                    ->where('rule_key', $ruleKey)
                    ->whereDate('dedupe_date', $todayLocal)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                $timeColumn = "{$period}_reminder_time";

                $dailyLogs = DB::table('activity_type_daily_logs')
                    ->join('activity_types', 'activity_types.id', '=', 'activity_type_daily_logs.activity_type_id')
                    ->where('activity_type_daily_logs.day_number', $day)
                    ->where('activity_type_daily_logs.notification_enabled', true)
                    ->where("activity_type_daily_logs.{$period}_reminder_enabled", true)
                    ->whereNotNull("activity_type_daily_logs.{$timeColumn}")
                    ->select('activity_types.type_key', 'activity_types.name', "activity_type_daily_logs.{$timeColumn} as trigger_time")
                    ->get()
                    ->filter(function ($log) use ($currentMinutes, $timeOffset) {
                        $parts = explode(':', $log->trigger_time);
                        $triggerMinutes = ((int)$parts[0] * 60 + (int)$parts[1]) + $timeOffset;
                        
                        // Strict notification window: Current time must be within 5 mins strictly AFTER trigger time.
                        $diff = $currentMinutes - $triggerMinutes;
                        if ($diff < 0) $diff += 1440; // handle midnight wrap
                        
                        return $diff >= 0 && $diff <= 5;
                    });

                if ($dailyLogs->isEmpty()) {
                    continue;
                }

                $pendingActivities = [];

                foreach ($dailyLogs as $log) {
                    $completed = Activity::where('user_id', $user->id)
                        ->where('type', $log->type_key)
                        ->whereDate('completed_at', $todayLocal)
                        ->where('is_completed', true)
                        ->exists();

                    if (!$completed) {
                        $pendingActivities[] = $log->name;
                    }
                }

                if (!empty($pendingActivities)) {
                    $count = count($pendingActivities);
                    
                    // Fallback hardcoded defaults if DB is missing
                    $title = "Momentum Reminder: Day {$day}";
                    if ($count === 1) {
                        $body = "Don't forget to complete your '{$pendingActivities[0]}' task today to keep your streak!";
                    } else {
                        $body = "You have {$count} tasks pending for today. Open the app to stay on track!";
                    }

                    // If Setting is present, override the values
                    if ($setting) {
                        if ($setting->default_title) {
                            $title = str_replace('{day}', (string)$day, $setting->default_title);
                            $title = str_replace('{count}', (string)$count, $title);
                        }
                        if ($setting->default_body) {
                            $tasksString = implode(', ', $pendingActivities);
                            $body = str_replace('{tasks}', $tasksString, $setting->default_body);
                            $body = str_replace('{day}', (string)$day, $body);
                            $body = str_replace('{count}', (string)$count, $body);
                            
                            // Smart replacement for "task vs tasks" logic
                            if ($count === 1) {
                                $body = str_replace('{single_task}', $pendingActivities[0], $body);
                            }
                        }
                    }

                    $tokens = UserPushToken::where('user_id', $user->id)->pluck('token');
                    if ($tokens->isEmpty()) {
                        continue;
                    }

                    $success = false;
                    foreach ($tokens as $token) {
                        if ($fcm->sendToToken($token, $title, $body, [
                            'type' => 'momentum_reminder',
                            'day' => (string) $day,
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ])) {
                            $success = true;
                        }
                    }

                    if ($success) {
                        $remindersSent++;
                        $this->info("{$period} Reminder sent to User ID {$user->id} ({$user->email}) for {$count} tasks.");
                        
                        NotificationAutomationDedupe::create([
                            'user_id' => $user->id,
                            'rule_key' => $ruleKey,
                            'dedupe_date' => $todayLocal,
                        ]);
                    }
                }
            }
        }

        $this->info("Daily task reminders completed. Sent {$remindersSent} reminders.");
        return 0;
    }
}

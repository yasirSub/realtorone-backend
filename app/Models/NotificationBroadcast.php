<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationBroadcast extends Model
{
    protected $fillable = [
        'title',
        'body',
        'display_style',
        'audience',
        'tier',
        'target_user_ids',
        'scheduled_at',
        'recurrence_type',
        'recurrence_time',
        'recurrence_day_of_week',
        'timezone',
        'status',
        'next_run_at',
        'last_run_at',
        'created_by',
        'deep_link',
        'extra_data',
        'last_sent_count',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'target_user_ids' => 'array',
            'extra_data' => 'array',
            'scheduled_at' => 'datetime',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

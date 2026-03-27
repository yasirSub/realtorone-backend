<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAutomationDedupe extends Model
{
    protected $table = 'notification_automation_dedupes';

    protected $fillable = [
        'user_id',
        'rule_key',
        'dedupe_date',
    ];

    protected function casts(): array
    {
        return [
            'dedupe_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

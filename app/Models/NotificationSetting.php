<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'default_title',
        'default_body',
        'trigger_settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'trigger_settings' => 'array',
    ];
}

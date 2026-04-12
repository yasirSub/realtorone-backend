<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webinar extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'zoom_link',
        'image_url',
        'scheduled_at',
        'is_active',
        'is_promotional',
        'target_tier',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_active' => 'boolean',
        'is_promotional' => 'boolean',
    ];
}

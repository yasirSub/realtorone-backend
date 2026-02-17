<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'category',
        'duration_minutes',
        'scheduled_at',
        'is_completed',
        'completed_at',
        'notes',
        'points',
        'quantity',
        'value'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'points' => 'integer',
        'quantity' => 'integer',
        'value' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

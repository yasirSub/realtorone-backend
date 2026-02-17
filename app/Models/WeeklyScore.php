<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeeklyScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'week_start',
        'week_end',
        'avg_score',
        'total_activities',
        'days_active',
        'consistency_percent',
        'total_leads',
        'total_deals',
        'total_commission',
        'streak_maintained',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'total_commission' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

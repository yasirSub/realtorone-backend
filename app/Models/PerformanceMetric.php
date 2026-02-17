<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'subconscious_score',
        'conscious_score',
        'results_score',
        'total_momentum_score',
        'leads_generated',
        'deals_closed',
        'commission_earned',
        'streak_count'
    ];

    protected $casts = [
        'date' => 'date',
        'subconscious_score' => 'integer',
        'conscious_score' => 'integer',
        'results_score' => 'integer',
        'total_momentum_score' => 'integer',
        'leads_generated' => 'integer',
        'deals_closed' => 'integer',
        'commission_earned' => 'decimal:2',
        'streak_count' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

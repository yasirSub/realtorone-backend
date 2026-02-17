<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderboardCache extends Model
{
    use HasFactory;

    protected $table = 'leaderboard_cache';

    protected $fillable = [
        'user_id',
        'category',
        'period',
        'period_date',
        'score',
        'rank',
        'metadata',
    ];

    protected $casts = [
        'period_date' => 'date',
        'score' => 'integer',
        'rank' => 'integer',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

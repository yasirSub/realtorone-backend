<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type_key',
        'category',
        'points',
        'is_global',
        'min_tier',
        'user_id',
        'icon',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_global' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

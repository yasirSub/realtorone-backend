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
        'script_idea',
        'type_key',
        'category',
        'section_title',
        'section_order',
        'item_order',
        'points',
        'is_global',
        'min_tier',
        'user_id',
        'icon',
    ];

    protected $casts = [
        'points' => 'integer',
        'is_global' => 'boolean',
        'section_order' => 'integer',
        'item_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

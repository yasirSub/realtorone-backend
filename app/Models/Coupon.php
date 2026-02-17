<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_percentage',
        'expires_at',
        'max_uses',
        'used_count',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'discount_percentage' => 'integer',
        'max_uses' => 'integer',
        'used_count' => 'integer',
    ];
}

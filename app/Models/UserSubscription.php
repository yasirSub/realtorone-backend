<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'started_at',
        'expires_at',
        'status',
        'payment_method',
        'payment_id',
        'amount_paid',
        'coupon_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount_paid' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class, 'package_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}


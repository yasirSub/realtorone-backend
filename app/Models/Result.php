<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'type',
        'client_name',
        'property_name',
        'source',
        'value',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'value' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }
}

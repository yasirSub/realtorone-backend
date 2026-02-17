<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'result_id',
        'client_name',
        'contact_info',
        'due_at',
        'completed_at',
        'is_completed',
        'is_overdue',
        'notes',
        'priority',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'is_overdue' => 'boolean',
        'priority' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function result()
    {
        return $this->belongsTo(Result::class);
    }
}

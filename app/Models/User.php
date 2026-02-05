<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'remember_token',
        // Profile fields
        'mobile',
        'city',
        'brokerage',
        'instagram',
        'linkedin',
        'years_experience',
        'current_monthly_income',
        'target_monthly_income',
        'profile_photo_path',
        'is_profile_complete',
        // Diagnosis fields
        'has_completed_diagnosis',
        'diagnosis_blocker',
        'diagnosis_scores',
        // Performance metrics
        'growth_score',
        'execution_rate',
        'mindset_index',
        'rank',
        // Streak tracking
        'current_streak',
        'longest_streak',
        'last_activity_date',
        // Premium status
        'is_premium',
        'premium_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_profile_complete' => 'boolean',
            'has_completed_diagnosis' => 'boolean',
            'is_premium' => 'boolean',
            'premium_expires_at' => 'datetime',
            'diagnosis_scores' => 'array',
            'years_experience' => 'integer',
            'current_monthly_income' => 'decimal:2',
            'target_monthly_income' => 'decimal:2',
            'growth_score' => 'integer',
            'execution_rate' => 'integer',
            'mindset_index' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
        ];
    }
}

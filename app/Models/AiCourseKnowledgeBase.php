<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiCourseKnowledgeBase extends Model
{
    protected $fillable = [
        'course_id',
        'tier',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}


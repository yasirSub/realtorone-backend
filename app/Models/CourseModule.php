<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model
{
    protected $fillable = ['course_id', 'title', 'description', 'sequence', 'is_free', 'is_published'];

    protected $casts = [
        'is_published' => 'boolean',
        'is_free' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(CourseLesson::class);
    }
}

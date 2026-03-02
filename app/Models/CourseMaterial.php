<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    protected $fillable = [
        'course_lesson_id', 
        'title',
        'type', 
        'url',
        'thumbnail_url',
        'show_download_link',
        'subtitle_source',
        'subtitles_url',
        'audio_language',
        'settings',
        'count'
    ];

    protected $casts = [
        'settings' => 'array',
        'show_download_link' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }
}

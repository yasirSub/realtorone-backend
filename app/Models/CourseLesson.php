<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseLesson extends Model
{
    protected $fillable = [
        'course_module_id', 
        'title', 
        'description',
        'sequence', 
        'is_published', 
        'allow_comments', 
        'is_preview',
        'allow_video_download',
        'allow_pdf_download'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'allow_comments' => 'boolean',
        'is_preview' => 'boolean',
        'allow_video_download' => 'boolean',
        'allow_pdf_download' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function materials()
    {
        return $this->hasMany(CourseMaterial::class);
    }
}

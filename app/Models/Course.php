<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    protected $fillable = ['title', 'description', 'thumbnail_url', 'url', 'min_tier', 'module_number', 'sequence', 'is_published'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function modules()
    {
        return $this->hasMany(CourseModule::class);
    }
}

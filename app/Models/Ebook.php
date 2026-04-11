<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ebook extends Model
{
    protected $fillable = [
        'title',
        'description',
        'thumbnail_url',
        'file_url',
        'min_tier',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}

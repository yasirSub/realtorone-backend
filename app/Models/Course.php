<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    protected $fillable = ['title', 'description', 'url', 'min_tier', 'module_number', 'sequence'];
}

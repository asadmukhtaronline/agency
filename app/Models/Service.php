<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'title',
        'slug',
        'icon',
        'short_description',
        'description',
        'image',
        'features',
        'technologies',
        'seo_title',
        'seo_description',
        'is_active',
        'order',
        'tags'
    ];
    
    protected $casts = [
        'features' => 'array',
        'technologies' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
}
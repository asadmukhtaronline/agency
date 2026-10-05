<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PortfolioItem extends Model
{
    use SoftDeletes;
    
    protected $table = 'portfolio_items';
    
    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'client_name',
        'project_url',
        'featured_image',
        'gallery',
        'technologies',
        'challenges',
        'solutions',
        'results',
        'completion_date',
        'is_featured',
        'is_active',
        'order'
    ];
    
    protected $casts = [
        'gallery' => 'array',
        'technologies' => 'array',
        'challenges' => 'array',
        'solutions' => 'array',
        'results' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'completion_date' => 'date',
        'order' => 'integer'
    ];
}
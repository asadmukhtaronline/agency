<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogPost extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'gallery',
        'tags',
        'seo_title',
        'seo_description',
        'views',
        'is_featured',
        'is_published',
        'published_at'
    ];
    
    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'gallery' => 'array',
        'published_at' => 'datetime'
    ];
    
    // Relationship with category
    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }
    
    // Scope for published posts
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
    
    // Scope for featured posts
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'short_description',
        'description',
        'image',
        'gallery',
        'features',
        'specifications',
        'tags',
        'seo_title',
        'seo_description',
        'is_active',
        'order',
        'category_id'  // ← Add this
    ];
    
    protected $casts = [
        'gallery' => 'array',
        'features' => 'array',
        'specifications' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
    
    // Relationship with category
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
}
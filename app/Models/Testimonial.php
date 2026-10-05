<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;
    
    protected $table = 'testimonials';
    
    protected $fillable = [
        'client_name',
        'client_position',
        'client_company',
        'client_avatar',
        'content',
        'rating',
        'service_used',
        'is_featured',
        'is_active',
        'order'
    ];
    
    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
    
    // Accessor for star rating display
    public function getStarRatingAttribute()
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '★';
            } else {
                $stars .= '☆';
            }
        }
        return $stars;
    }
    
    // Scope for active testimonials
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Scope for featured testimonials
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    // Scope for high rated testimonials
    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4);
    }
}
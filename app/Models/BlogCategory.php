<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class, 'category_id');
    }
}
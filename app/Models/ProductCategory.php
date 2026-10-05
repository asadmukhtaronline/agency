<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;
    
    protected $table = 'product_categories';
    
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    // Relationship with products
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
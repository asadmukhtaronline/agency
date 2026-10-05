<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceType extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
        'order'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
    
    // Relationship with pricing plans
    public function pricingPlans()
    {
        return $this->hasMany(PricingPlan::class, 'service_type_id');
    }
    
    // Scope for active types
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
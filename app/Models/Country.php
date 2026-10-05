<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'code',
        'currency_code',
        'currency_symbol',
        'flag',
        'is_active',
        'phone_number',     // ye add karein
        'whatsapp_number',  // ye add karein
        'email'            // ye add karein
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    // Relationship with pricing plans
    public function pricingPlans()
    {
        return $this->hasMany(PricingPlan::class);
    }
    
    // Scope for active countries
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Accessor for full currency
    public function getFullCurrencyAttribute()
    {
        return $this->currency_code . ' (' . $this->currency_symbol . ')';
    }
}
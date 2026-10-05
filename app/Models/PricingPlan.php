<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingPlan extends Model
{
    use SoftDeletes;
    
    protected $table = 'pricing_plans';
    
    protected $fillable = [
        'country_id',
        'service_type_id',  // Add this
        'name',
        'slug',
        'icon',
        'price',
        'period',
        'features',
        'is_popular',
        'button_text',
        'button_link',
        'order',
        'is_active'
    ];
    
    protected $casts = [
        'features' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
}
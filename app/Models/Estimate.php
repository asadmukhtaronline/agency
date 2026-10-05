<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estimate extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'client_id',
        'country_id',
        'currency_symbol',
        'service_type_id',
        'pricing_plan_id',
        'estimate_number',
        'title',
        'description',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'status',
        'is_paid',
        'paid_at',
        'valid_until',
        'notes',
        'terms_conditions',
         'pdf_path',  // Add this
        'created_by'
    ];
    
    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'valid_until' => 'date',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime'
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
    
    public function pricingPlan()
    {
        return $this->belongsTo(PricingPlan::class);
    }
    
    public function items()
    {
        return $this->hasMany(EstimateItem::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public static function generateNumber()
    {
        $last = self::latest()->first();
        $number = 'EST-' . date('Y') . '-' . str_pad(($last ? $last->id + 1 : 1), 4, '0', STR_PAD_LEFT);
        return $number;
    }
}
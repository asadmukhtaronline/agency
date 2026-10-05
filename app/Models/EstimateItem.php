<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstimateItem extends Model
{
    protected $fillable = [
        'estimate_id',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'total'
    ];
    
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2'
    ];
    
    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
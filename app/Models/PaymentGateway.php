<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentGateway extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'code',
        'icon',
        'settings',
        'is_active',
        'order'
    ];
    
    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
}
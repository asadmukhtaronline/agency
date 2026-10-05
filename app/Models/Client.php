<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'country_id',
        'name',
        'email',
        'phone',
        'company',
        'address',
        'city',
        'state',
        'postal_code',
        'gst_vat',
        'notes',
        'avatar',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function estimate(){
        return $this->hasMany(estimate::class);
    }
    
    public function quotations()
    {
        return $this->hasMany(Estimate::class);
    }
    
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
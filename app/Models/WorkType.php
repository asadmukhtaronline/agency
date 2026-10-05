<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkType extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'description',
        'order',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
    
    // Add this relationship
    public function leads()
    {
        return $this->hasMany(ClientLead::class, 'work_type_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
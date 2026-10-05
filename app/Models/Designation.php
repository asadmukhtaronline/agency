<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Designation extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'description',
        'order',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer'
    ];
    
    // Relationship with Team Members
    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }
    
    // Scope for active designations
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
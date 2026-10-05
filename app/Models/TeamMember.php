<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeamMember extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'designation_id',
        'name',
        'email',
        'phone',
        'avatar',
        'bio',
        'skills',
        'facebook',
        'twitter',
        'linkedin',
        'instagram',
        'github',
        'experience_years',
        'education',
        'achievements',
        'order',
        'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
        'experience_years' => 'integer',
        'skills' => 'array'
    ];
    
    // Relationship with Designation
    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }
    
    // Get full name
    public function getFullNameAttribute()
    {
        return $this->name;
    }
    
    // Get avatar URL
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=FE171B&color=fff';
    }
    
    // Get skills as array
    public function getSkillsArrayAttribute()
    {
        if (is_array($this->skills)) {
            return $this->skills;
        }
        if ($this->skills) {
            return explode(',', $this->skills);
        }
        return [];
    }
    
    // Scope for active members
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
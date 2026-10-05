<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'phone',
        'bio',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // Check if user is super admin
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    // Check if user is admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is editor
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    // Get role badge class
    public function getRoleBadgeAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'bg-danger',
            'admin' => 'bg-primary',
            'editor' => 'bg-info',
            'viewer' => 'bg-secondary',
            default => 'bg-secondary'
        };
    }

    // Get role label
    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
            default => ucfirst($this->role)
        };
    }

    // Get status badge
    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_active) {
            return '<span class="badge bg-success">Active</span>';
        }
        return '<span class="badge bg-danger">Inactive</span>';
    }
}
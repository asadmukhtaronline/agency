<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;
    
    protected $table = 'quotations';
    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'service_type',
        'budget',
        'message',
        'requirements',
        'status',
        'quoted_amount',
        'admin_notes',
        'quoted_at'
    ];
    
    protected $casts = [
        'requirements' => 'array',
        'quoted_amount' => 'decimal:2',
        'quoted_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_QUOTED = 'quoted';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    
    // Get status badge color
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_REVIEWED => 'info',
            self::STATUS_QUOTED => 'primary',
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary'
        };
    }
}
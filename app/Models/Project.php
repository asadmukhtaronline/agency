<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'client_id',
        'service_type_id',
        'project_code',
        'name',
        'description',
        'project_type',
        'payment_type',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'status',
        'priority',
        'progress',
        'start_date',
        'end_date',
        'notes',
        'created_by'
    ];
    
    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'progress' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }
    
    // Add this relationship
    public function payments()
    {
        return $this->hasMany(ProjectPayment::class);
    }
    
    public static function generateCode()
    {
        $last = self::latest()->first();
        $number = 'PRJ-' . date('Y') . '-' . str_pad(($last ? $last->id + 1 : 1), 4, '0', STR_PAD_LEFT);
        return $number;
    }
    
    public function updateProgress()
    {
        $completedMilestones = $this->milestones()->where('status', 'completed')->count();
        $totalMilestones = $this->milestones()->count();
        $progress = ($totalMilestones > 0) ? round(($completedMilestones / $totalMilestones) * 100) : 0;
        $this->update(['progress' => $progress]);
    }
    
    public function updatePaymentStatus()
    {
        $paidAmount = $this->payments()->sum('amount');
        $this->update([
            'paid_amount' => $paidAmount,
            'due_amount' => $this->total_amount - $paidAmount,
            'payment_status' => $paidAmount >= $this->total_amount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid')
        ]);
    }
}
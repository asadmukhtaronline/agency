<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectMilestone extends Model
{
    use SoftDeletes;
    
    protected $table = 'project_milestones';
    
    protected $fillable = [
        'project_id',
        'milestone_name',
        'description',
        'amount',
        'due_date',
        'status',
        'payment_status',
        'completed_at'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'completed_at' => 'datetime'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function payments()
    {
        return $this->hasMany(ProjectPayment::class, 'milestone_id');
    }
    
    public function updatePaymentStatus()
    {
        $paidAmount = $this->payments()->sum('amount');
        $this->update([
            'payment_status' => $paidAmount >= $this->amount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid')
        ]);
        
        if ($this->project) {
            $this->project->updatePaymentStatus();
        }
    }
}
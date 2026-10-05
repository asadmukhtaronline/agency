<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPayment extends Model
{
    use SoftDeletes;
    
    protected $table = 'project_payments';
    
    protected $fillable = [
        'project_id',
        'milestone_id',  // Note: this is milestone_id, not project_milestone_id
        'payment_gateway_id',
        'payment_number',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'receipt_number',
        'invoice_pdf',
        'notes',
        'status',
        'created_by'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date'
    ];
    
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    
    public function milestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');  // Specify foreign key
    }
    
    public function paymentGateway()
    {
        return $this->belongsTo(PaymentGateway::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public static function generateNumber()
    {
        $last = self::latest()->first();
        $number = 'PAY-' . date('Y') . '-' . str_pad(($last ? $last->id + 1 : 1), 5, '0', STR_PAD_LEFT);
        return $number;
    }
}
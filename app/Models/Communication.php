<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Communication extends Model
{
    protected $fillable = [
        'client_lead_id',
        'type',
        'direction',
        'subject',
        'notes',
        'client_response',
        'duration',
        'next_followup',
        'is_completed'
    ];
    
    protected $casts = [
        'next_followup' => 'date',
        'is_completed' => 'boolean'
    ];
    
    public function clientLead()
    {
        return $this->belongsTo(ClientLead::class, 'client_lead_id');
    }
}
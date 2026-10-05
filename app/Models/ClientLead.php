<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientLead extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'company_name',
        'contact_person',
        'email',
        'phone',
        'alternative_phone',
        'has_whatsapp',
        'whatsapp_number',
        'website',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'pinterest',
        'reddit',
        'youtube',
        'tiktok',
        'country_id',
        'country_code',
        'screenshots',
        'client_type_id',
        'work_type_id',
        'requirements',
        'notes',
        'status',
        'priority',
        'next_followup',
        'contacted_at',
        'meeting_at',
        'address',
        'city',
        'source',
        'lead_score',
        'is_active'
    ];
    
    protected $casts = [
        'has_whatsapp' => 'boolean',
        'is_active' => 'boolean',
        'screenshots' => 'array',
        'next_followup' => 'date',
        'contacted_at' => 'datetime',
        'meeting_at' => 'datetime'
    ];
    
    // Relationships
    public function clientType()
    {
        return $this->belongsTo(ClientType::class, 'client_type_id');
    }
    
    public function workType()
    {
        return $this->belongsTo(WorkType::class, 'work_type_id');
    }
    
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    
    public function communications()
    {
        return $this->hasMany(Communication::class, 'client_lead_id')->orderBy('created_at', 'desc');
    }
    
    // Add this method - Get Social Links Array
    public function getSocialLinksAttribute()
    {
        $links = [];
        if ($this->facebook) $links['facebook'] = $this->facebook;
        if ($this->instagram) $links['instagram'] = $this->instagram;
        if ($this->twitter) $links['twitter'] = $this->twitter;
        if ($this->linkedin) $links['linkedin'] = $this->linkedin;
        if ($this->pinterest) $links['pinterest'] = $this->pinterest;
        if ($this->reddit) $links['reddit'] = $this->reddit;
        if ($this->youtube) $links['youtube'] = $this->youtube;
        if ($this->tiktok) $links['tiktok'] = $this->tiktok;
        if ($this->whatsapp) $links['whatsapp'] = $this->whatsapp;
        return $links;
    }
    
    // Status Badge Helper
    public function getStatusBadgeAttribute()
    {
        $statuses = [
            'new' => ['label' => 'New', 'class' => 'bg-primary'],
            'contacted' => ['label' => 'Contacted', 'class' => 'bg-info'],
            'meeting' => ['label' => 'Meeting', 'class' => 'bg-warning'],
            'proposal' => ['label' => 'Proposal', 'class' => 'bg-secondary'],
            'negotiation' => ['label' => 'Negotiation', 'class' => 'bg-dark'],
            'won' => ['label' => 'Won', 'class' => 'bg-success'],
            'lost' => ['label' => 'Lost', 'class' => 'bg-danger'],
        ];
        
        return $statuses[$this->status] ?? ['label' => $this->status, 'class' => 'bg-secondary'];
    }
    
    // Priority Badge Helper
    public function getPriorityBadgeAttribute()
    {
        $priorities = [
            'low' => ['label' => 'Low', 'class' => 'bg-secondary'],
            'medium' => ['label' => 'Medium', 'class' => 'bg-warning'],
            'high' => ['label' => 'High', 'class' => 'bg-danger'],
        ];
        
        return $priorities[$this->priority] ?? ['label' => $this->priority, 'class' => 'bg-secondary'];
    }
}
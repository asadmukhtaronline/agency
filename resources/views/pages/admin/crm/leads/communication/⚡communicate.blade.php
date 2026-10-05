<?php

use Livewire\Component;
use App\Models\ClientLead;
use App\Models\Communication;

new class extends Component
{
    public $leadId;
    public $lead;
    
    // Form fields
    public $type = 'call';
    public $direction = 'outgoing';
    public $subject = '';
    public $notes = '';
    public $client_response = '';
    public $duration = '';
    public $next_followup = '';
    
    public function mount($id)
    {
        $this->leadId = $id;
        $this->lead = ClientLead::find($id);
        
        if (!$this->lead) {
            session()->flash('error', 'Lead not found!');
            return redirect()->route('admin.crm.leads.index');
        }
    }
    
    public function save()
    {
        $this->validate([
            'type' => 'required|in:call,email,meeting,whatsapp',
            'direction' => 'required|in:outgoing,incoming',
            'subject' => 'nullable|max:255',
            'notes' => 'nullable|string',
            'client_response' => 'nullable|string',
            'duration' => 'nullable|integer|min:1',
            'next_followup' => 'nullable|date',
        ]);
        
        Communication::create([
            'client_lead_id' => $this->leadId,
            'type' => $this->type,
            'direction' => $this->direction,
            'subject' => $this->subject,
            'notes' => $this->notes,
            'client_response' => $this->client_response,
            'duration' => $this->duration,
            'next_followup' => $this->next_followup,
            'is_completed' => true,
        ]);
        
        // Update lead status based on response
        if ($this->client_response == 'interested') {
            $this->lead->status = 'contacted';
            $this->lead->save();
        } elseif ($this->client_response == 'not_interested') {
            $this->lead->status = 'lost';
            $this->lead->is_active = false;
            $this->lead->save();
        } elseif ($this->client_response == 'call_back') {
            $this->lead->status = 'contacted';
            $this->lead->save();
        }
        
        session()->flash('message', 'Communication recorded successfully!');
        
        // Reset form
        $this->reset(['type', 'direction', 'subject', 'notes', 'client_response', 'duration', 'next_followup']);
    }
    
    public function getCommunicationsProperty()
    {
        return Communication::where('client_lead_id', $this->leadId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
};

?>

<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Communication Log</h2>
            <p class="text-muted">
                <i class="fas fa-building me-1"></i> {{ $lead->company_name }} 
                @if($lead->contact_person)
                    | <i class="fas fa-user me-1"></i> {{ $lead->contact_person }}
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('admin.crm.leads.show', $leadId) }}" class="btn btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Lead
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Add Communication Form -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i> Record New Communication
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form wire:submit.prevent="save">
                        <!-- Communication Type -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Communication Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="call" value="call" wire:model="type">
                                    <label class="form-check-label" for="call">
                                        <i class="fas fa-phone text-success"></i> Call
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="email" value="email" wire:model="type">
                                    <label class="form-check-label" for="email">
                                        <i class="fas fa-envelope text-primary"></i> Email
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="meeting" value="meeting" wire:model="type">
                                    <label class="form-check-label" for="meeting">
                                        <i class="fas fa-handshake text-warning"></i> Meeting
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="whatsapp" value="whatsapp" wire:model="type">
                                    <label class="form-check-label" for="whatsapp">
                                        <i class="fab fa-whatsapp text-success"></i> WhatsApp
                                    </label>
                                </div>
                            </div>
                            @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Direction -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Direction <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="outgoing" value="outgoing" wire:model="direction">
                                    <label class="form-check-label" for="outgoing">
                                        <i class="fas fa-arrow-up text-success"></i> Outgoing (We called/sent)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="incoming" value="incoming" wire:model="direction">
                                    <label class="form-check-label" for="incoming">
                                        <i class="fas fa-arrow-down text-info"></i> Incoming (Client called)
                                    </label>
                                </div>
                            </div>
                            @error('direction') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Subject -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control rounded-3 @error('subject') is-invalid @enderror" 
                                   wire:model="subject" placeholder="e.g., Proposal discussion, Follow-up call">
                            @error('subject') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Duration (only for calls) -->
                        @if($type == 'call')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Duration (minutes)</label>
                                <input type="number" class="form-control rounded-3 @error('duration') is-invalid @enderror" 
                                       wire:model="duration" placeholder="e.g., 15">
                                @error('duration') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif
                        
                        <!-- Client Response -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Client Response</label>
                            <select class="form-select rounded-3 @error('client_response') is-invalid @enderror" wire:model="client_response">
                                <option value="">Select Response</option>
                                <option value="interested">✅ Interested - Positive</option>
                                <option value="call_back">📞 Call Back Later</option>
                                <option value="follow_up">🔄 Follow-up Needed</option>
                                <option value="not_interested">❌ Not Interested</option>
                                <option value="no_response">🤷 No Response</option>
                            </select>
                            @error('client_response') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Notes -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control rounded-3 @error('notes') is-invalid @enderror" 
                                      wire:model="notes" rows="3" 
                                      placeholder="What was discussed? Any important points?"></textarea>
                            @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Next Follow-up -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Next Follow-up Date</label>
                            <input type="date" class="form-control rounded-3 @error('next_followup') is-invalid @enderror" 
                                   wire:model="next_followup">
                            @error('next_followup') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">When should we contact this client next?</small>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3">
                                <i class="fas fa-save me-2"></i> Save Communication
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Communication History -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i> Communication History
                        <span class="badge bg-secondary ms-2">{{ $this->communications->count() }} records</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($this->communications->count() > 0)
                        <div class="p-4">
                            @foreach($this->communications as $comm)
                                <div class="communication-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="d-flex align-items-center gap-2">
                                            <!-- Icon based on type -->
                                            @if($comm->type == 'call')
                                                <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                                    <i class="fas fa-phone text-success"></i>
                                                </div>
                                            @elseif($comm->type == 'email')
                                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle">
                                                    <i class="fas fa-envelope text-primary"></i>
                                                </div>
                                            @elseif($comm->type == 'meeting')
                                                <div class="bg-warning bg-opacity-10 p-2 rounded-circle">
                                                    <i class="fas fa-handshake text-warning"></i>
                                                </div>
                                            @else
                                                <div class="bg-success bg-opacity-10 p-2 rounded-circle">
                                                    <i class="fab fa-whatsapp text-success"></i>
                                                </div>
                                            @endif
                                            
                                            <div>
                                                <strong class="text-capitalize">{{ $comm->type }}</strong>
                                                @if($comm->direction == 'outgoing')
                                                    <span class="badge bg-info ms-1">Outgoing</span>
                                                @else
                                                    <span class="badge bg-secondary ms-1">Incoming</span>
                                                @endif
                                                <div class="text-muted small">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    {{ \Carbon\Carbon::parse($comm->created_at)->format('M d, Y h:i A') }}
                                                    @if($comm->duration)
                                                        | <i class="fas fa-clock me-1"></i> {{ $comm->duration }} min
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        @if($comm->client_response)
                                            <span class="badge 
                                                @if($comm->client_response == 'interested') bg-success
                                                @elseif($comm->client_response == 'not_interested') bg-danger
                                                @elseif($comm->client_response == 'call_back') bg-warning text-dark
                                                @else bg-secondary
                                                @endif">
                                                @if($comm->client_response == 'interested') ✅
                                                @elseif($comm->client_response == 'not_interested') ❌
                                                @elseif($comm->client_response == 'call_back') 📞
                                                @else 🔄
                                                @endif
                                                {{ ucfirst(str_replace('_', ' ', $comm->client_response)) }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($comm->subject)
                                        <div class="mt-2 ms-4 ps-3 border-start border-3 border-primary">
                                            <strong>Subject:</strong> {{ $comm->subject }}
                                        </div>
                                    @endif
                                    
                                    @if($comm->notes)
                                        <div class="mt-2 ms-4 ps-3">
                                            <i class="fas fa-comment text-muted me-1"></i>
                                            <span class="text-muted">{{ $comm->notes }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($comm->next_followup)
                                        <div class="mt-2 ms-4 ps-3">
                                            <i class="fas fa-bell text-warning me-1"></i>
                                            <span class="small">
                                                Next follow-up: 
                                                <strong>{{ \Carbon\Carbon::parse($comm->next_followup)->format('M d, Y') }}</strong>
                                                @if(\Carbon\Carbon::parse($comm->next_followup)->isToday())
                                                    <span class="badge bg-warning text-dark ms-1">Today</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No communications yet</h5>
                            <p class="text-muted">Record your first call, email, or meeting with this client</p>
                            <div class="mt-3">
                                <i class="fas fa-arrow-up text-primary"></i>
                                <span class="text-muted"> Use the form on the left to get started</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .form-control:focus, .form-select:focus {
        border-color: #FE171B;
        box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
        border: none;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
    }
    .btn-secondary {
        transition: transform 0.2s;
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .communication-item:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
</style>
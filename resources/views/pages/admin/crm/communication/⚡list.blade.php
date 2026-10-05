<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Communication Log</h2>
            <p class="text-muted">{{ $lead->company_name }} - Record calls, emails, meetings</p>
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
                    <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Record Communication</h5>
                </div>
                <div class="card-body p-4">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Communication Type</label>
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
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Direction</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="outgoing" value="outgoing" wire:model="direction">
                                    <label class="form-check-label" for="outgoing">📤 Outgoing (We called/sent)</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="incoming" value="incoming" wire:model="direction">
                                    <label class="form-check-label" for="incoming">📥 Incoming (Client called)</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subject</label>
                            <input type="text" class="form-control rounded-3" wire:model="subject" placeholder="e.g., Proposal discussion, Follow-up call">
                        </div>
                        
                        @if($type == 'call')
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Duration (minutes)</label>
                                <input type="number" class="form-control rounded-3" wire:model="duration" placeholder="e.g., 15">
                            </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Client Response</label>
                            <select class="form-select rounded-3" wire:model="client_response">
                                <option value="">Select Response</option>
                                <option value="interested">✅ Interested - Positive</option>
                                <option value="call_back">📞 Call Back Later</option>
                                <option value="follow_up">🔄 Follow-up Needed</option>
                                <option value="not_interested">❌ Not Interested</option>
                                <option value="no_response">🤷 No Response</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea class="form-control rounded-3" wire:model="notes" rows="3" 
                                      placeholder="What was discussed? Any important points?"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Next Follow-up Date</label>
                            <input type="date" class="form-control rounded-3" wire:model="next_followup">
                        </div>
                        
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
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Communication History</h5>
                </div>
                <div class="card-body p-0">
                    @if($communications->count() > 0)
                        <div class="p-4">
                            @foreach($communications as $comm)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            @if($comm->type == 'call')
                                                <i class="fas fa-phone text-success fa-lg me-2"></i>
                                            @elseif($comm->type == 'email')
                                                <i class="fas fa-envelope text-primary fa-lg me-2"></i>
                                            @elseif($comm->type == 'meeting')
                                                <i class="fas fa-handshake text-warning fa-lg me-2"></i>
                                            @else
                                                <i class="fab fa-whatsapp text-success fa-lg me-2"></i>
                                            @endif
                                            
                                            <strong>{{ ucfirst($comm->type) }}</strong>
                                            @if($comm->direction == 'outgoing')
                                                <span class="badge bg-info">Outgoing</span>
                                            @else
                                                <span class="badge bg-secondary">Incoming</span>
                                            @endif
                                            
                                            <div class="text-muted small mt-1">
                                                {{ \Carbon\Carbon::parse($comm->created_at)->format('M d, Y h:i A') }}
                                                @if($comm->duration)
                                                    | Duration: {{ $comm->duration }} min
                                                @endif
                                            </div>
                                        </div>
                                        @if($comm->client_response)
                                            <span class="badge 
                                                @if($comm->client_response == 'interested') bg-success
                                                @elseif($comm->client_response == 'not_interested') bg-danger
                                                @elseif($comm->client_response == 'call_back') bg-warning
                                                @else bg-secondary
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $comm->client_response)) }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    @if($comm->subject)
                                        <div class="mt-2">
                                            <strong>Subject:</strong> {{ $comm->subject }}
                                        </div>
                                    @endif
                                    
                                    @if($comm->notes)
                                        <div class="mt-1 text-muted">
                                            <i class="fas fa-comment"></i> {{ $comm->notes }}
                                        </div>
                                    @endif
                                    
                                    @if($comm->next_followup)
                                        <div class="mt-2">
                                            <i class="fas fa-calendar-alt text-info"></i>
                                            <span class="small">Next follow-up: {{ \Carbon\Carbon::parse($comm->next_followup)->format('M d, Y') }}</span>
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
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .border-bottom:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
</style>
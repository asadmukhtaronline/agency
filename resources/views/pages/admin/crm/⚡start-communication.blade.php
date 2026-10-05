<?php

use Livewire\Component;
use App\Models\ClientLead;
use App\Models\Communication;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    
    // Form fields
    public $showModal = false;
    public $clientId = null;
    public $client = null;
    public $type = 'call';
    public $direction = 'outgoing';
    public $subject = '';
    public $notes = '';
    public $client_response = '';
    public $duration = '';
    public $next_followup = '';
    
    // Filters
    public $search = '';
    public $work_type_filter = '';
    
    public function openCommunicationForm($id)
    {
        $this->clientId = $id;
        $this->client = ClientLead::find($id);
        $this->reset(['type', 'direction', 'subject', 'notes', 'client_response', 'duration', 'next_followup']);
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['clientId', 'client', 'type', 'direction', 'subject', 'notes', 'client_response', 'duration', 'next_followup']);
    }
    
    public function saveCommunication()
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
            'client_lead_id' => $this->clientId,
            'type' => $this->type,
            'direction' => $this->direction,
            'subject' => $this->subject,
            'notes' => $this->notes,
            'client_response' => $this->client_response,
            'duration' => $this->duration,
            'next_followup' => $this->next_followup,
            'is_completed' => true,
        ]);
        
        // Update lead status
        $lead = ClientLead::find($this->clientId);
        if ($this->client_response == 'interested') {
            $lead->status = 'contacted';
            $lead->save();
        } elseif ($this->client_response == 'not_interested') {
            $lead->status = 'lost';
            $lead->is_active = false;
            $lead->save();
        } elseif ($lead->status == 'new') {
            $lead->status = 'contacted';
            $lead->save();
        }
        
        session()->flash('message', 'Communication recorded successfully!');
        $this->closeModal();
    }
    
    // Get today's follow-ups
    public function getTodayFollowupsProperty()
    {
        return ClientLead::whereHas('communications', function($q) {
            $q->whereDate('next_followup', now()->toDateString());
        })->with(['communications' => function($q) {
            $q->latest()->first();
        }, 'workType'])->get();
    }
    
    // Get new leads (never contacted)
    public function getNewLeadsProperty()
    {
        return ClientLead::whereDoesntHave('communications')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->with('workType')
            ->get();
    }
    
    // Get other active leads
    public function getOtherLeadsProperty()
    {
        $query = ClientLead::where('is_active', true)
            ->whereHas('communications')
            ->whereDoesntHave('communications', function($q) {
                $q->whereDate('next_followup', now()->toDateString());
            });
        
        
        if ($this->search) {
            $query->where('company_name', 'like', '%'.$this->search.'%');
        }
        
        if ($this->work_type_filter) {
            $query->where('work_type_id', $this->work_type_filter);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->with(['workType', 'communications' => function($q) {
                $q->latest()->first();
            }])
            ->paginate(10);
    }
    
    // Get work types for filter
    public function getWorkTypesProperty()
    {
        return \App\Models\WorkType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function render()
    {
        return <<<'HTML'
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
                    <h2 class="fw-bold mb-0"><i class="fas fa-phone-alt me-2"></i> Start Communication</h2>
                    <p class="text-muted">Today's follow-ups and pending communications</p>
                </div>
                <div class="text-muted">
                    <i class="fas fa-calendar-alt me-1"></i> {{ now()->format('l, F d, Y') }}
                </div>
            </div>
            
            <!-- Today's Follow-ups Section -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0">
                        <i class="fas fa-bell me-2"></i> Today's Follow-ups 
                        <span class="badge bg-light text-dark ms-2">{{ $this->todayFollowups->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($this->todayFollowups->count() > 0)
                        @foreach($this->todayFollowups as $lead)
                            @php $lastComm = $lead->communications->first(); @endphp
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="fas fa-building text-primary"></i>
                                            <strong class="fs-5">{{ $lead->company_name }}</strong>
                                            @if($lead->workType)
                                                <span class="badge" style="background-color: {{ $lead->workType->color }}">
                                                    {{ $lead->workType->name }}
                                                </span>
                                            @endif
                                            <span class="badge bg-danger">Follow-up Today</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if($lead->contact_person)
                                                    <small><i class="fas fa-user text-muted"></i> {{ $lead->contact_person }}</small><br>
                                                @endif
                                                @if($lead->phone)
                                                    <small><i class="fas fa-phone text-muted"></i> {{ $lead->phone }}</small>
                                                @endif
                                                @if($lead->email)
                                                    <br><small><i class="fas fa-envelope text-muted"></i> {{ $lead->email }}</small>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($lastComm)
                                                    <small class="text-muted">
                                                        <i class="fas fa-history"></i> Last: {{ ucfirst($lastComm->type) }} 
                                                        on {{ \Carbon\Carbon::parse($lastComm->created_at)->format('M d') }}
                                                        @if($lastComm->client_response)
                                                            - Response: {{ str_replace('_', ' ', $lastComm->client_response) }}
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-primary rounded-3">
                                            <i class="fas fa-phone me-1"></i> Call
                                        </button>
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-info rounded-3">
                                            <i class="fas fa-envelope me-1"></i> Email
                                        </button>
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-success rounded-3">
                                            <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                            <h5 class="text-muted">No follow-ups for today!</h5>
                            <p class="text-muted">All caught up. Great job!</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- New Leads Section (Never Contacted) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0">
                        <i class="fas fa-star me-2"></i> New Leads (Never Contacted)
                        <span class="badge bg-light text-dark ms-2">{{ $this->newLeads->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($this->newLeads->count() > 0)
                        @foreach($this->newLeads as $lead)
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="fas fa-building text-success"></i>
                                            <strong class="fs-5">{{ $lead->company_name }}</strong>
                                            @if($lead->workType)
                                                <span class="badge" style="background-color: {{ $lead->workType->color }}">
                                                    {{ $lead->workType->name }}
                                                </span>
                                            @endif
                                            <span class="badge bg-success">New</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if($lead->contact_person)
                                                    <small><i class="fas fa-user text-muted"></i> {{ $lead->contact_person }}</small><br>
                                                @endif
                                                @if($lead->phone)
                                                    <small><i class="fas fa-phone text-muted"></i> {{ $lead->phone }}</small>
                                                @endif
                                                @if($lead->email)
                                                    <br><small><i class="fas fa-envelope text-muted"></i> {{ $lead->email }}</small>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar-alt"></i> Added: {{ $lead->created_at->format('M d, Y') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-primary rounded-3">
                                            <i class="fas fa-phone me-1"></i> Call
                                        </button>
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-info rounded-3">
                                            <i class="fas fa-envelope me-1"></i> Email
                                        </button>
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-success rounded-3">
                                            <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-smile fa-3x text-muted mb-2"></i>
                            <h5 class="text-muted">No new leads!</h5>
                            <p class="text-muted">All leads have been contacted</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Other Active Leads Section -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light rounded-top-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i> All Active Leads - {{$this->otherLeads->count()}}</h5>
                        <div class="d-flex gap-2">
                            <input type="text" wire:model.live="search" class="form-control form-control-sm rounded-3" 
                                   placeholder="Search company..." style="width: 200px;">
                            <select wire:model.live="work_type_filter" class="form-select form-select-sm rounded-3" style="width: 150px;">
                                <option value="">All Work Types</option>
                                @foreach($this->workTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    
                    @if($this->otherLeads->count() > 0)
                        @foreach($this->otherLeads as $lead)
                            @php $lastComm = $lead->communications->first(); @endphp
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i class="fas fa-building text-secondary"></i>
                                            <strong>{{ $lead->company_name }}</strong>
                                            @if($lead->workType)
                                                <span class="badge" style="background-color: {{ $lead->workType->color }}">
                                                    {{ $lead->workType->name }}
                                                </span>
                                            @endif
                                            <span class="badge bg-secondary">{{ ucfirst($lead->status) }}</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                @if($lead->contact_person)
                                                    <small><i class="fas fa-user text-muted"></i> {{ $lead->contact_person }}</small><br>
                                                @endif
                                                @if($lead->phone)
                                                    <small><i class="fas fa-phone text-muted"></i> {{ $lead->phone }}</small>
                                                @endif
                                            </div>
                                            <div class="col-md-6">
                                                @if($lastComm)
                                                    <small class="text-muted">
                                                        <i class="fas fa-history"></i> Last: {{ ucfirst($lastComm->type) }} 
                                                        on {{ \Carbon\Carbon::parse($lastComm->created_at)->format('M d') }}
                                                        @if($lastComm->next_followup)
                                                            | Next: {{ \Carbon\Carbon::parse($lastComm->next_followup)->format('M d') }}
                                                        @endif
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="btn-group">
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-primary rounded-3">
                                            <i class="fas fa-phone me-1"></i>
                                        </button>
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-info rounded-3">
                                            <i class="fas fa-envelope me-1"></i>
                                        </button>
                                        <button wire:click="openCommunicationForm({{ $lead->id }})" class="btn btn-sm btn-success rounded-3">
                                            <i class="fab fa-whatsapp me-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="p-3">
                            {{ $this->otherLeads->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                            <h5 class="text-muted">No other leads found</h5>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Communication Modal -->
            @if($showModal && $client)
            <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050; overflow-y: auto;">
                <div class="modal-dialog modal-dialog-centered modal-md">
                    <div class="modal-content rounded-4">
                        <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                            <h5 class="modal-title">
                                <i class="fas fa-comment-dots me-2"></i> Communication with {{ $client->company_name }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3 bg-light p-2 rounded-3">
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> {{ $client->contact_person ?? 'No contact person' }} |
                                    <i class="fas fa-phone"></i> {{ $client->phone ?? 'No phone' }} |
                                    <i class="fas fa-envelope"></i> {{ $client->email ?? 'No email' }}
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Communication Type</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="call" value="call" wire:model="type">
                                        <label for="call"><i class="fas fa-phone text-success"></i> Call</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="email" value="email" wire:model="type">
                                        <label for="email"><i class="fas fa-envelope text-primary"></i> Email</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="meeting" value="meeting" wire:model="type">
                                        <label for="meeting"><i class="fas fa-handshake text-warning"></i> Meeting</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="whatsapp" value="whatsapp" wire:model="type">
                                        <label for="whatsapp"><i class="fab fa-whatsapp text-success"></i> WhatsApp</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Direction</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="outgoing" value="outgoing" wire:model="direction">
                                        <label for="outgoing">📤 Outgoing (We called/sent)</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="incoming" value="incoming" wire:model="direction">
                                        <label for="incoming">📥 Incoming (Client called)</label>
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
                                <textarea class="form-control rounded-3" wire:model="notes" rows="3" placeholder="What was discussed? Any important points?"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Next Follow-up Date</label>
                                <input type="date" class="form-control rounded-3" wire:model="next_followup">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary rounded-3" wire:click="closeModal">Cancel</button>
                            <button type="button" class="btn btn-primary rounded-3" wire:click="saveCommunication">
                                <i class="fas fa-save me-2"></i> Save Communication
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
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
                transition: transform 0.2s;
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
            .btn-group .btn {
                margin: 0 2px;
            }
            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 1050;
            }
        </style>
        HTML;
    }
};
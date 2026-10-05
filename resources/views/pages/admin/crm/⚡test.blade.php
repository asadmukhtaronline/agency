<?php

use Livewire\Component;
use App\Models\Communication;
use App\Models\ClientLead;
use App\Models\ClientType;
use App\Models\WorkType;

new class extends Component
{
    public $communications;
    
    // Filters
    public $search = '';
    public $type_filter = '';
    public $response_filter = '';
    public $client_filter = '';
    public $work_type_filter = '';
    public $client_type_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $direction_filter = '';
    
    public function mount()
    {
        $this->loadCommunications();
    }
    
    public function loadCommunications()
    {
        $query = Communication::with(['clientLead', 'clientLead.clientType', 'clientLead.workType'])
            ->orderBy('created_at', 'desc');
        
        // Search by company name
        if ($this->search) {
            $query->whereHas('clientLead', function($q) {
                $q->where('company_name', 'like', '%'.$this->search.'%')
                  ->orWhere('contact_person', 'like', '%'.$this->search.'%');
            });
        }
        
        // Filter by communication type (call, email, meeting, whatsapp)
        if ($this->type_filter) {
            $query->where('type', $this->type_filter);
        }
        
        // Filter by direction (outgoing, incoming)
        if ($this->direction_filter) {
            $query->where('direction', $this->direction_filter);
        }
        
        // Filter by client response
        if ($this->response_filter) {
            $query->where('client_response', $this->response_filter);
        }
        
        // Filter by specific client
        if ($this->client_filter) {
            $query->where('client_lead_id', $this->client_filter);
        }
        
        // Filter by work type (New Website, Bug Fixing, etc.)
        if ($this->work_type_filter) {
            $query->whereHas('clientLead', function($q) {
                $q->where('work_type_id', $this->work_type_filter);
            });
        }
        
        // Filter by client type (Construction, Law, etc.)
        if ($this->client_type_filter) {
            $query->whereHas('clientLead', function($q) {
                $q->where('client_type_id', $this->client_type_filter);
            });
        }
        
        // Filter by date range
        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }
        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }
        
        $this->communications = $query->get();
    }
    
    // Reset all filters
    public function resetFilters()
    {
        $this->search = '';
        $this->type_filter = '';
        $this->response_filter = '';
        $this->client_filter = '';
        $this->work_type_filter = '';
        $this->client_type_filter = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->direction_filter = '';
        $this->loadCommunications();
    }
    
    // Watch for filter changes
    public function updatedSearch() { $this->loadCommunications(); }
    public function updatedTypeFilter() { $this->loadCommunications(); }
    public function updatedResponseFilter() { $this->loadCommunications(); }
    public function updatedClientFilter() { $this->loadCommunications(); }
    public function updatedWorkTypeFilter() { $this->loadCommunications(); }
    public function updatedClientTypeFilter() { $this->loadCommunications(); }
    public function updatedDateFrom() { $this->loadCommunications(); }
    public function updatedDateTo() { $this->loadCommunications(); }
    public function updatedDirectionFilter() { $this->loadCommunications(); }
    
    // Get data for dropdowns
    public function getClientsProperty()
    {
        return ClientLead::where('is_active', true)->orderBy('company_name')->get();
    }
    
    public function getWorkTypesProperty()
    {
        return WorkType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getClientTypesProperty()
    {
        return ClientType::where('is_active', true)->orderBy('name')->get();
    }
};

?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">All Communications</h2>
            <p class="text-muted">Complete history of all client interactions</p>
        </div>
        <div>
            <button wire:click="resetFilters" class="btn btn-secondary rounded-3">
                <i class="fas fa-undo-alt me-2"></i> Reset Filters
            </button>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters Row 1 -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Search Client</label>
                    <input type="text" wire:model.live="search" class="form-control rounded-3" 
                           placeholder="Company or contact name...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Communication Type</label>
                    <select wire:model.live="type_filter" class="form-select rounded-3">
                        <option value="">All Types</option>
                        <option value="call">📞 Call</option>
                        <option value="email">✉️ Email</option>
                        <option value="meeting">🤝 Meeting</option>
                        <option value="whatsapp">💬 WhatsApp</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Direction</label>
                    <select wire:model.live="direction_filter" class="form-select rounded-3">
                        <option value="">All</option>
                        <option value="outgoing">📤 Outgoing</option>
                        <option value="incoming">📥 Incoming</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Client Response</label>
                    <select wire:model.live="response_filter" class="form-select rounded-3">
                        <option value="">All Responses</option>
                        <option value="interested">✅ Interested</option>
                        <option value="call_back">📞 Call Back</option>
                        <option value="follow_up">🔄 Follow-up</option>
                        <option value="not_interested">❌ Not Interested</option>
                        <option value="no_response">🤷 No Response</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold">Select Client</label>
                    <select wire:model.live="client_filter" class="form-select rounded-3">
                        <option value="">All Clients</option>
                        @foreach($this->clients as $client)
                            <option value="{{ $client->id ?? 'N/A' }}">{{ $client->company_name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Filters Row 2 -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Work Type</label>
                    <select wire:model.live="work_type_filter" class="form-select rounded-3">
                        <option value="">All Work Types</option>
                        @foreach($this->workTypes as $type)
                            <option value="{{ $type->id ?? 'N/A' }}">{{ $type->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Client Type (Industry)</label>
                    <select wire:model.live="client_type_filter" class="form-select rounded-3">
                        <option value="">All Industries</option>
                        @foreach($this->clientTypes as $type)
                            <option value="{{ $type->id ?? 'N/A' }}">{{ $type->name ?? 'N/A' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date From</label>
                    <input type="date" wire:model.live="date_from" class="form-control rounded-3">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Date To</label>
                    <input type="date" wire:model.live="date_to" class="form-control rounded-3">
                </div>
            </div>
            
            <!-- Active Filters Display -->
            @if($search || $type_filter || $response_filter || $client_filter || $work_type_filter || $client_type_filter || $date_from || $date_to || $direction_filter)
                <div class="mb-3">
                    <div class="alert alert-light rounded-3">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="text-muted">Active Filters:</span>
                            @if($search)<span class="badge bg-primary">Search: {{ $search ?? 'N/A' }}</span>@endif
                            @if($type_filter)<span class="badge bg-info">{{ ucfirst($type_filter ?? 'N/A') }}</span>@endif
                            @if($direction_filter)<span class="badge bg-info">{{ ucfirst($direction_filter ?? 'N/A') }}</span>@endif
                            @if($response_filter)<span class="badge bg-warning">{{ str_replace('_', ' ', $response_filter ?? 'N/A') }}</span>@endif
                            @if($client_filter)<span class="badge bg-success">Specific Client</span>@endif
                            @if($work_type_filter)<span class="badge bg-secondary">Work Type</span>@endif
                            @if($client_type_filter)<span class="badge bg-secondary">Industry</span>@endif
                            @if($date_from)<span class="badge bg-dark">From: {{ $date_from ?? 'N/A' }}</span>@endif
                            @if($date_to)<span class="badge bg-dark">To: {{ $date_to ?? 'N/A' }}</span>@endif
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Stats Summary -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="bg-light rounded-3 p-3 text-center">
                        <h4 class="mb-0">{{ $communications->count() ?? 'N/A' }}</h4>
                        <small class="text-muted">Total Records</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light rounded-3 p-3 text-center">
                        <h4 class="mb-0 text-success">{{ $communications->where('client_response', 'interested')->count() ?? 'N/A' }}</h4>
                        <small class="text-muted">Interested</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light rounded-3 p-3 text-center">
                        <h4 class="mb-0 text-danger">{{ $communications->where('client_response', 'not_interested')->count() ?? 'N/A' }}</h4>
                        <small class="text-muted">Not Interested</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="bg-light rounded-3 p-3 text-center">
                        <h4 class="mb-0 text-warning">{{ $communications->whereNotNull('next_followup')->where('next_followup', '>=', now())->count() ?? 'N/A' }}</h4>
                        <small class="text-muted">Pending Follow-ups</small>
                    </div>
                </div>
            </div>
            
            <!-- Communications Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="px-3">Date & Time</th>
                            <th class="px-3">Client / Industry / Work</th>
                            <th class="px-3">Type</th>
                            <th class="px-3">Direction</th>
                            <th class="px-3">Subject / Notes</th>
                            <th class="px-3">Response</th>
                            <th class="px-3">Next Follow-up</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($communications as $comm)
                        <tr>
                            <td class="px-3">
                                <small>{{ \Carbon\Carbon::parse($comm->created_at ?? 'N/A')->format('M d, Y h:i A') }}</small>
                             </td>
                            <td class="px-3">
                                <strong>{{ $comm->clientLead->company_name ?? 'N/A' }}</strong>
                                @if($comm->clientLead->contact_person ?? 'N/A')
                                    <br><small class="text-muted"><i class="fas fa-user"></i> {{ $comm->clientLead->contact_person ?? 'N/A' }}</small>
                                @endif
                                @if($comm->clientLead->workType ?? 'N/A')
                                    <br><small class="text-muted"><i class="fas fa-tag"></i> {{ $comm->clientLead->workType->name ?? 'N/A' }}</small>
                                @endif
                                @if($comm->clientLead->clientType ?? 'N/A')
                                    <br><small class="text-muted"><i class="fas fa-building"></i> {{ $comm->clientLead->clientType->name ?? 'N/A' }}</small>
                                @endif
                             </td>
                            <td class="px-3">
                                @if($comm->type == 'call')
                                    <i class="fas fa-phone text-success"></i> Call
                                @elseif($comm->type == 'email')
                                    <i class="fas fa-envelope text-primary"></i> Email
                                @elseif($comm->type == 'meeting')
                                    <i class="fas fa-handshake text-warning"></i> Meeting
                                @else
                                    <i class="fab fa-whatsapp text-success"></i> WhatsApp
                                @endif
                                @if($comm->duration ?? 'N/A')
                                    <br><small class="text-muted">{{ $comm->duration ?? 'N/A' }} min</small>
                                @endif
                             </td>
                            <td class="px-3">
                                @if($comm->direction == 'outgoing')
                                    <span class="badge bg-info">Outgoing</span>
                                @else
                                    <span class="badge bg-secondary">Incoming</span>
                                @endif
                             </td>
                            <td class="px-3">
                                @if($comm->subject ?? 'N/A')
                                    <strong>{{ Str::limit($comm->subject ?? 'N/A', 30) }}</strong>
                                    <br>
                                @endif
                                <small class="text-muted">{{ Str::limit($comm->notes ?? 'N/A', 50) ?? '-' }}</small>
                             </td>
                            <td class="px-3">
                                @if($comm->client_response ?? 'N/A')
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
                                        {{ str_replace('_', ' ', $comm->client_response ?? 'N/A') }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                             </td>
                            <td class="px-3">
                                @if($comm->next_followup ?? 'N/A')
                                    <small>{{ \Carbon\Carbon::parse($comm->next_followup ?? 'N/A')->format('M d, Y') }}</small>
                                    @if(\Carbon\Carbon::parse($comm->next_followup ?? 'N/A')->isToday())
                                        <span class="badge bg-warning text-dark ms-1">Today</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                             </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-comments fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No communications found</h5>
                                    <p class="text-muted">Try changing your filters or add some communications.</p>
                                    <button wire:click="resetFilters" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-undo-alt me-2"></i> Reset Filters
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #FE171B;
        box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
    }
    .table td {
        vertical-align: middle;
    }
    .btn-secondary {
        transition: transform 0.2s;
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
</style>
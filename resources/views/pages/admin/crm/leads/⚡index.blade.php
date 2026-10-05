<?php

use Livewire\Component;
use App\Models\ClientLead;
use App\Models\ClientType;
use App\Models\WorkType;
use App\Models\Country;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    
    // Filters
    public $search = '';
    public $status_filter = '';
    public $priority_filter = '';
    public $client_type_filter = '';
    public $work_type_filter = '';
    public $country_filter = '';
    public $perPage = 10;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status_filter' => ['except' => ''],
        'priority_filter' => ['except' => ''],
        'client_type_filter' => ['except' => ''],
        'work_type_filter' => ['except' => ''],
        'country_filter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatedPriorityFilter()
    {
        $this->resetPage();
    }
    
    public function updatedClientTypeFilter()
    {
        $this->resetPage();
    }
    
    public function updatedWorkTypeFilter()
    {
        $this->resetPage();
    }
    
    public function updatedCountryFilter()
    {
        $this->resetPage();
    }
    
    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->search = '';
        $this->status_filter = '';
        $this->priority_filter = '';
        $this->client_type_filter = '';
        $this->work_type_filter = '';
        $this->country_filter = '';
        $this->perPage = 10;
        $this->resetPage();
    }
    
    public function deleteLead($id)
    {
        $lead = ClientLead::find($id);
        if ($lead) {
            // Delete screenshots if exist
            if ($lead->screenshots) {
                $screenshots = json_decode($lead->screenshots, true);
                if ($screenshots) {
                    foreach ($screenshots as $screenshot) {
                        if (\Storage::disk('public')->exists($screenshot)) {
                            \Storage::disk('public')->delete($screenshot);
                        }
                    }
                }
            }
            $lead->delete();
            session()->flash('message', 'Lead deleted successfully!');
        }
    }
    
    public function updateStatus($id, $status)
    {
        $lead = ClientLead::find($id);
        if ($lead) {
            $lead->status = $status;
            if ($status == 'contacted' && !$lead->contacted_at) {
                $lead->contacted_at = now();
            }
            if ($status == 'won' || $status == 'lost') {
                $lead->is_active = false;
            }
            $lead->save();
            session()->flash('message', 'Status updated successfully!');
        }
    }
    
    // Computed properties for dropdowns
    public function getClientTypesProperty()
    {
        return ClientType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getWorkTypesProperty()
    {
        return WorkType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getLeadsProperty()
    {
        $query = ClientLead::with(['clientType', 'workType', 'country']);
        
        // Search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('company_name', 'like', '%'.$this->search.'%')
                  ->orWhere('contact_person', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('phone', 'like', '%'.$this->search.'%')
                  ->orWhere('alternative_phone', 'like', '%'.$this->search.'%')
                  ->orWhere('whatsapp_number', 'like', '%'.$this->search.'%');
            });
        }
        
        // Status filter
        if (!empty($this->status_filter)) {
            $query->where('status', $this->status_filter);
        }
        
        // Priority filter
        if (!empty($this->priority_filter)) {
            $query->where('priority', $this->priority_filter);
        }
        
        // Client Type filter
        if (!empty($this->client_type_filter)) {
            $query->where('client_type_id', $this->client_type_filter);
        }
        
        // Work Type filter
        if (!empty($this->work_type_filter)) {
            $query->where('work_type_id', $this->work_type_filter);
        }
        
        // Country filter
        if (!empty($this->country_filter)) {
            $query->where('country_id', $this->country_filter);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Client Leads</h2>
            <p class="text-muted">Manage all your client leads for hunting</p>
        </div>
        <div>
            <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary rounded-3">
                <i class="fas fa-plus-circle me-2"></i> Add New Lead
            </a>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters Row -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by company, contact, email, phone...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <select wire:model.live="status_filter" class="form-select rounded-3">
                        <option value="">All Status</option>
                        <option value="new">🆕 New</option>
                        <option value="contacted">📞 Contacted</option>
                        <option value="meeting">🤝 Meeting</option>
                        <option value="proposal">📄 Proposal</option>
                        <option value="negotiation">💬 Negotiation</option>
                        <option value="won">✅ Won</option>
                        <option value="lost">❌ Lost</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select wire:model.live="priority_filter" class="form-select rounded-3">
                        <option value="">All Priority</option>
                        <option value="low">🟢 Low</option>
                        <option value="medium">🟡 Medium</option>
                        <option value="high">🔴 High</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select wire:model.live="client_type_filter" class="form-select rounded-3">
                        <option value="">All Client Types</option>
                        @foreach($this->clientTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select wire:model.live="work_type_filter" class="form-select rounded-3">
                        <option value="">All Work Types</option>
                        @foreach($this->workTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-1">
                    <select wire:model.live="perPage" class="form-select rounded-3">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <!-- Second Row Filters -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <select wire:model.live="country_filter" class="form-select rounded-3">
                        <option value="">All Countries</option>
                        @foreach($this->countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-9">
                    @if($search || $status_filter || $priority_filter || $client_type_filter || $work_type_filter || $country_filter)
                        <button wire:click="resetFilters" class="btn btn-sm btn-secondary rounded-3">
                            <i class="fas fa-undo-alt me-1"></i> Reset All Filters
                        </button>
                    @endif
                </div>
            </div>    
        </div>
                    <!-- Leads Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                           <th class="py-3 px-3"><i class="fas fa-building"></i> Company</th>
                            <th class="py-3 px-3"><i class="fas fa-user-tag"></i> Industry </th>
                            <th class="py-3 px-3"><i class="fas fa-briefcase"></i> Type</th>
                            <th class="py-3 px-3"><i class="fas fa-globe"></i> Country</th>
                            <th class="py-3 px-3"><i class="fas fa-toggle-on"></i> Status</th>
                            <th class="py-3 px-3 text-center"><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->leads as $lead)
                        <tr>
                            <td class="px-3">
                                <strong>{{ $lead->company_name }}</strong>
                                @if($lead->website)
                                    <br><small><a href="{{ $lead->website }}" target="_blank" class="text-decoration-none">
                                        <i class="fas fa-globe"></i> {{ Str::limit($lead->website, 25) }}
                                    </a></small>
                                @endif
                            </td>
                            <td class="px-3">
                                @if($lead->clientType)
                                    <span class="badge" style="background-color: {{ $lead->clientType->color }}">
                                        {{ $lead->clientType->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="px-3">
                                @if($lead->workType)
                                    <span class="badge" style="background-color: {{ $lead->workType->color }}">
                                        {{ $lead->workType->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="px-3">
                                @if($lead->country)
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-flag"></i> {{ $lead->country->name }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="px-3">
                                <select wire:change="updateStatus({{ $lead->id }}, $event.target.value)" class="form-select form-select-sm" style="width: auto; min-width: 110px;">
                                    <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>🆕 New</option>
                                    <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>📞 Contacted</option>
                                    <option value="meeting" {{ $lead->status == 'meeting' ? 'selected' : '' }}>🤝 Meeting</option>
                                    <option value="proposal" {{ $lead->status == 'proposal' ? 'selected' : '' }}>📄 Proposal</option>
                                    <option value="negotiation" {{ $lead->status == 'negotiation' ? 'selected' : '' }}>💬 Negotiation</option>
                                    <option value="won" {{ $lead->status == 'won' ? 'selected' : '' }}>✅ Won</option>
                                    <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>❌ Lost</option>
                                </select>
                            </td>
                            <td class="px-3 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.crm.leads.show', $lead->id) }}" class="btn btn-sm btn-success rounded-3 me-1" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.crm.leads.edit', $lead->id) }}" class="btn btn-sm btn-info rounded-3 me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button wire:click="deleteLead({{ $lead->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure you want to delete this lead?')" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No leads found</h5>
                                    <a href="{{ route('admin.crm.leads.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Lead
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        <div class="card-body">
            
            <!-- Pagination & Stats -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    <i class="fas fa-info-circle me-1"></i>
                    Showing {{ $this->leads->firstItem() ?? 0 }} to {{ $this->leads->lastItem() ?? 0 }} of {{ $this->leads->total() }} leads
                </div>
                <div>
                    {{ $this->leads->links() }}
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
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .btn-sm {
        padding: 0.3rem 0.6rem;
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        padding: 5px 10px;
    }
    .btn-info, .btn-success, .btn-danger {
        transition: transform 0.2s;
    }
    .btn-info:hover, .btn-success:hover, .btn-danger:hover {
        transform: translateY(-1px);
    }
</style>
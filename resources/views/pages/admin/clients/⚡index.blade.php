<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client;
use App\Models\Country;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status_filter = '';
    public $country_filter = '';
    public $perPage = 10;
    
    public function deleteClient($id)
    {
        $client = Client::find($id);
        
        if (!$client) {
            session()->flash('error', 'Client not found!');
            return;
        }
        
        // Check for related records
        $quotationsCount = $client->quotations()->count();
        $estimatesCount = $client->estimate()->count();
        $projectsCount = $client->projects()->count();
        
        $hasRelations = false;
        $errorMessage = 'Cannot delete this client because: ';
        $errors = [];
        
        if ($quotationsCount > 0) {
            $errors[] = "$quotationsCount quotation(s) exist";
            $hasRelations = true;
        }
        
        if ($estimatesCount > 0) {
            $errors[] = "$estimatesCount estimate(s) exist";
            $hasRelations = true;
        }
        
        if ($projectsCount > 0) {
            $errors[] = "$projectsCount project(s) exist";
            $hasRelations = true;
        }
        
        if ($hasRelations) {
            $errorMessage .= implode(', ', $errors) . '. Please delete these records first.';
            session()->flash('error', $errorMessage);
            return;
        }
        
        // Delete avatar if exists
        if ($client->avatar && \Storage::disk('public')->exists($client->avatar)) {
            \Storage::disk('public')->delete($client->avatar);
        }
        
        $client->delete();
        session()->flash('message', 'Client deleted successfully!');
    }
    
    public function toggleStatus($id)
    {
        $client = Client::find($id);
        $client->is_active = !$client->is_active;
        $client->save();
        session()->flash('message', 'Client status updated!');
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatingCountryFilter()
    {
        $this->resetPage();
    }
    
    public function getClientsProperty()
    {
        $query = Client::query()->with('country');
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('company', 'like', '%'.$this->search.'%')
                  ->orWhere('phone', 'like', '%'.$this->search.'%');
            });
        }
        
        if ($this->status_filter !== '') {
            $query->where('is_active', $this->status_filter);
        }
        
        if ($this->country_filter) {
            $query->where('country_id', $this->country_filter);
        }
        
        return $query->orderBy('name')->paginate($this->perPage);
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
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
            <h2 class="fw-bold mb-0">Client Management</h2>
            <p class="text-muted">Manage your clients and their information</p>
        </div>
        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Client
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by name, email, company or phone...">
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select rounded-3" wire:model.live="country_filter">
                        <option value="">All Countries</option>
                        @foreach($this->countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select rounded-3" wire:model.live="perPage">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
            
            <!-- Clients Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-user text-primary me-2"></i> Client</th>
                            <th class="py-3 px-4"><i class="fas fa-globe text-primary me-2"></i> Country</th>
                            <th class="py-3 px-4"><i class="fas fa-building text-primary me-2"></i> Company</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->clients as $client)
                        <tr>
                            <td class="px-4">
                                 @if($client->avatar)
                                    <img src="{{ asset('storage/' . $client->avatar) }}" width="40" height="40" class="rounded-circle" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user fa-lg text-muted"></i>
                                    </div>
                                @endif
                                <strong>{{ $client->name }}</strong>
                            </td>
                            <td class="px-4">
                                @if($client->country)
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-flag me-1"></i> {{ $client->country->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted">—</span>
                                @endif
                            </td>
                            <td class="px-4">{{ $client->company ?? '—' }}</td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $client->id }})" {{ $client->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-sm btn-success rounded-3 me-2" title="View">
                                        <i class="fas fa-eye me-1"></i> 
                                    </a>
                                    <a href="{{ route('admin.clients.edit', $client->id) }}" class="btn btn-sm btn-info rounded-3 me-2" title="Edit">
                                        <i class="fas fa-edit me-1"></i> 
                                    </a>
                                    <button wire:click="deleteClient({{ $client->id }})" class="btn btn-sm btn-danger rounded-3" title="Delete" onclick="return confirm('Are you sure you want to delete this client?')">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No clients found</h5>
                                    <a href="{{ route('admin.clients.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Client
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->clients->links() }}
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
    .input-group-text {
        border-right: none;
    }
    .form-control:focus {
        border-color: #FE171B;
        box-shadow: none;
    }
    .input-group:focus-within {
        box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
        border-radius: 12px;
    }
    .badge {
        font-weight: 500;
        padding: 4px 8px;
    }
</style>
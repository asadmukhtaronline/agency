<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Estimate;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status_filter = '';
    public $perPage = 10;
    
    public function deleteEstimate($id)
    {
        $estimate = Estimate::find($id);
        if ($estimate) {
            // Delete related items first
            $estimate->items()->delete();
            $estimate->delete();
            session()->flash('message', 'Estimate deleted successfully!');
        }
    }
    
    public function updateStatus($id, $status)
    {
        $estimate = Estimate::find($id);
        $estimate->status = $status;
        $estimate->save();
        session()->flash('message', 'Estimate status updated!');
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function getEstimatesProperty()
    {
        $query = Estimate::with(['client', 'creator']);
        
        if ($this->search) {
            $query->where('estimate_number', 'like', '%'.$this->search.'%')
                  ->orWhere('title', 'like', '%'.$this->search.'%')
                  ->orWhereHas('client', function($q) {
                      $q->where('name', 'like', '%'.$this->search.'%');
                  });
        }
        
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        
        return $query->latest()->paginate($this->perPage);
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
            <h2 class="fw-bold mb-0">Estimates Management <i class="fa fa-spinner fa-spin" wire:loading></i> </h2>
            <p class="text-muted">Manage client estimates and quotations</p>
        </div>
        <a href="{{ route('admin.estimates.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Create New Estimate
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-5 mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by number, title or client...">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="accepted">Accepted</option>
                        <option value="expired">Expired</option>
                        <option value="converted">Converted</option>
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
            
            <!-- Estimates Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-file-invoice text-primary me-2"></i> Estimate #</th>
                            <th class="py-3 px-4"><i class="fas fa-user text-primary me-2"></i> Client</th>
                            <th class="py-3 px-4"><i class="fas fa-dollar-sign text-primary me-2"></i> Total</th>
                            <th class="py-3 px-4"><i class="fas fa-calendar text-primary me-2"></i> Valid Until</th>
                            <th class="py-3 px-4"><i class="fas fa-info-circle text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->estimates as $estimate)
                        <tr>
                            <td class="px-4">
                                <strong class="text-primary">{{ $estimate->estimate_number }}</strong>
                            </td>
                            <td class="px-4">
                                <strong>{{ $estimate->client->name ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">{{ $estimate->client->email ?? 'N/A' }}</small>
                            </td>
                            <td class="px-4">
                                <strong>${{ number_format($estimate->total, 2) }}</strong>
                            </td>
                            <td class="px-4">
                                @if($estimate->valid_until)
                                    <span class="{{ $estimate->valid_until < now() ? 'text-danger' : 'text-muted' }}">
                                        {{ $estimate->valid_until->format('M d, Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4">
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'sent' => 'info',
                                        'accepted' => 'success',
                                        'expired' => 'danger',
                                        'converted' => 'primary'
                                    ];
                                @endphp
                                <select class="form-select form-select-sm" wire:change="updateStatus({{ $estimate->id }}, $event.target.value)" style="width: 120px;">
                                    <option value="draft" {{ $estimate->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="sent" {{ $estimate->status == 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="accepted" {{ $estimate->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="expired" {{ $estimate->status == 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="converted" {{ $estimate->status == 'converted' ? 'selected' : '' }}>Converted</option>
                                </select>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.estimates.show', $estimate->id) }}" class="btn btn-sm btn-success rounded-3 me-2">
                                        <i class="fas fa-eye me-1"></i> 
                                    </a>
                                    <a href="{{ route('admin.estimates.edit', $estimate->id) }}" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> 
                                    </a>
                                    <button wire:click="deleteEstimate({{ $estimate->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No estimates found</h5>
                                    <a href="{{ route('admin.estimates.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Create Your First Estimate
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->estimates->links() }}
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
</style>
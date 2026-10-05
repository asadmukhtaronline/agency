<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Quotation;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status_filter = '';
    public $perPage = 10;
    public $showModal = false;
    public $selectedQuotation = null;
    public $admin_notes = '';
    public $quoted_amount = '';
    public $status = '';
    
    public function openModal($id)
    {
        $this->selectedQuotation = Quotation::find($id);
        $this->admin_notes = $this->selectedQuotation->admin_notes;
        $this->quoted_amount = $this->selectedQuotation->quoted_amount;
        $this->status = $this->selectedQuotation->status;
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedQuotation = null;
        $this->admin_notes = '';
        $this->quoted_amount = '';
        $this->status = '';
    }
    
    public function updateQuotation()
    {
        $quotation = Quotation::find($this->selectedQuotation->id);
        
        $quotation->update([
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'quoted_amount' => $this->quoted_amount ?: null,
            'quoted_at' => $this->status == 'quoted' ? now() : $quotation->quoted_at
        ]);
        
        $this->closeModal();
        session()->flash('message', 'Quotation updated successfully!');
    }
    
    public function deleteQuotation($id)
    {
        Quotation::find($id)->delete();
        session()->flash('message', 'Quotation deleted successfully!');
    }
    
    public function getQuotationsProperty()
    {
        $query = Quotation::query();
        
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('company', 'like', '%'.$this->search.'%');
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
            <h2 class="fw-bold mb-0">Quotations Management</h2>
            <p class="text-muted">Manage customer quote requests</p>
        </div>
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
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by name, email or company...">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="quoted">Quoted</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
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
            </div>
            <!-- Quotations Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-user text-primary me-2"></i> Customer</th>
                            <th class="py-3 px-4"><i class="fas fa-building text-primary me-2"></i> Company</th>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Service</th>
                            <th class="py-3 px-4"><i class="fas fa-clock text-primary me-2"></i> Date</th>
                            <th class="py-3 px-4"><i class="fas fa-info-circle text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->quotations as $quotation)
                        <tr>
                            <td class="px-4">
                                <strong>{{ $quotation->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $quotation->email }}</small>
                                @if($quotation->phone)
                                    <br><small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $quotation->phone }}</small>
                                @endif
                            </td>
                            <td class="px-4">{{ $quotation->company ?? '—' }}</td>
                            <td class="px-4">{{ $quotation->service_type ?? '—' }}</td>
                            <td class="px-4">
                                <small>{{ $quotation->created_at->format('M d, Y') }}</small>
                            </td>
                            <td class="px-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'reviewed' => 'info',
                                        'quoted' => 'primary',
                                        'accepted' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$quotation->status] }} rounded-pill">
                                    {{ ucfirst($quotation->status) }}
                                </span>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="openModal({{ $quotation->id }})" class="btn btn-sm btn-primary rounded-3 me-2">
                                        <i class="fas fa-eye me-1"></i> 
                                    </button>
                                    <button wire:click="deleteQuotation({{ $quotation->id }})" class="btn btn-sm btn-danger rounded-3" wire:confirm="Are you sure? You want to delete qoute.">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No quotations found</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-body">
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->quotations->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal for View/Update Quotation -->
    @if($showModal && $selectedQuotation)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Quotation Details #{{ $selectedQuotation->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Customer Information -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Customer Information</h6>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="fw-semibold text-muted">Name:</label>
                            <p>{{ $selectedQuotation->name }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="fw-semibold text-muted">Email:</label>
                            <p>{{ $selectedQuotation->email }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="fw-semibold text-muted">Phone:</label>
                            <p>{{ $selectedQuotation->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="fw-semibold text-muted">Company:</label>
                            <p>{{ $selectedQuotation->company ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="fw-semibold text-muted">Service Type:</label>
                            <p>{{ $selectedQuotation->service_type ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="fw-semibold text-muted">Request Date:</label>
                            <p>{{ $selectedQuotation->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <div class="mb-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">Customer Message</h6>
                        <div class="p-3 bg-light rounded-3">
                            {{ $selectedQuotation->message }}
                        </div>
                    </div>
                    
                    <!-- Requirements -->
                    @if($selectedQuotation->requirements)
                        <div class="mb-3">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">Requirements</h6>
                            <div class="p-3 bg-light rounded-3">
                                <pre class="mb-0" style="white-space: pre-wrap;">{{ json_encode($selectedQuotation->requirements, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Admin Section -->
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Admin Actions</h6>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select rounded-3" wire:model="status">
                            <option value="pending">Pending</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="quoted">Quoted</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quoted Amount ($)</label>
                        <input type="number" step="0.01" class="form-control rounded-3" wire:model="quoted_amount" placeholder="Enter amount">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Admin Notes</label>
                        <textarea class="form-control rounded-3" wire:model="admin_notes" rows="3" placeholder="Add internal notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" wire:click="closeModal">Close</button>
                    <button type="button" class="btn btn-primary rounded-3" wire:click="updateQuotation">
                        <i class="fas fa-save me-2"></i> Save Changes
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
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
    }
    pre {
        font-family: inherit;
        font-size: 0.9rem;
    }
</style>
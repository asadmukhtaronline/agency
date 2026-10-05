<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\QuoteRequest;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status_filter = '';
    public $perPage = 10;
    public $showModal = false;
    public $selectedQuote = null;
    public $admin_notes = '';
    public $quoted_amount = '';
    public $status = '';
    
    public function openModal($id)
    {
        $this->selectedQuote = QuoteRequest::with('pricingPlan')->find($id);
        $this->admin_notes = $this->selectedQuote->admin_notes;
        $this->quoted_amount = $this->selectedQuote->quoted_amount;
        $this->status = $this->selectedQuote->status;
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedQuote = null;
        $this->admin_notes = '';
        $this->quoted_amount = '';
        $this->status = '';
    }
    
    public function updateQuote()
    {
        $quote = QuoteRequest::find($this->selectedQuote->id);
        
        $quote->update([
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'quoted_amount' => $this->quoted_amount ?: null,
            'quoted_at' => $this->status == 'quoted' ? now() : $quote->quoted_at
        ]);
        
        $this->closeModal();
        session()->flash('message', 'Quote request updated successfully!');
    }
    
    public function deleteQuote($id)
    {
        QuoteRequest::find($id)->delete();
        session()->flash('message', 'Quote request deleted successfully!');
    }
    
    public function getQuotesProperty()
    {
        $query = QuoteRequest::query();
        
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('plan_name', 'like', '%'.$this->search.'%');
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
            <h2 class="fw-bold mb-0">Pricing Quote Requests</h2>
            <p class="text-muted">Manage customer pricing plan quote requests</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning rounded-pill px-3 py-2">
                <i class="fas fa-clock me-1"></i> Pending: {{ App\Models\QuoteRequest::where('status', 'pending')->count() }}
            </span>
            <span class="badge bg-success rounded-pill px-3 py-2">
                <i class="fas fa-check-circle me-1"></i> Quoted: {{ App\Models\QuoteRequest::where('status', 'quoted')->count() }}
            </span>
            <span class="badge bg-danger rounded-pill px-3 py-2">
                <i class="fas fa-times-circle me-1"></i> Rejected: {{ App\Models\QuoteRequest::where('status', 'rejected')->count() }}
            </span>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-5 mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by name, email or plan...">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="contacted">Contacted</option>
                        <option value="quoted">Quoted</option>
                        <option value="converted">Converted</option>
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
        
        <!-- Quotes Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4"><i class="fas fa-user text-primary me-2"></i> Customer</th>
                        <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Plan Details</th>
                        <th class="py-3 px-4"><i class="fas fa-dollar-sign text-primary me-2"></i> Quoted Amount</th>
                        <th class="py-3 px-4"><i class="fas fa-clock text-primary me-2"></i> Date</th>
                        <th class="py-3 px-4"><i class="fas fa-info-circle text-primary me-2"></i> Status</th>
                        <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->quotes as $quote)
                    <tr>
                        <td class="px-4">
                            <strong>{{ $quote->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $quote->email }}</small>
                            @if($quote->phone)
                                <br><small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $quote->phone }}</small>
                            @endif
                        </td>
                        <td class="px-4">
                            <span class="fw-semibold text-primary">{{ $quote->plan_name ?? 'Custom Quote' }}</span>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-layer-group me-1"></i>
                                {{ $quote->pricingPlan?->service_type?->name ?? 'General' }}
                            </small>
                            <br>
                            <small class="text-muted">{{ Str::limit($quote->message, 40) }}</small>
                        </td>
                        <td class="px-4">
                            @if($quote->quoted_amount)
                                <span class="fw-bold text-success">${{ number_format($quote->quoted_amount, 2) }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4">
                            <small>{{ $quote->created_at->format('M d, Y') }}</small>
                            <br>
                            <small class="text-muted">{{ $quote->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="px-4">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'contacted' => 'info',
                                    'quoted' => 'primary',
                                    'converted' => 'success',
                                    'rejected' => 'danger'
                                ];
                                $statusIcons = [
                                    'pending' => 'fa-clock',
                                    'contacted' => 'fa-phone',
                                    'quoted' => 'fa-file-invoice-dollar',
                                    'converted' => 'fa-check-circle',
                                    'rejected' => 'fa-times-circle'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$quote->status] ?? 'secondary' }} rounded-pill px-3 py-2">
                                <i class="fas {{ $statusIcons[$quote->status] ?? 'fa-info-circle' }} me-1"></i>
                                {{ ucfirst($quote->status) }}
                            </span>
                        </td>
                        <td class="px-4 text-center">
                            <div class="btn-group" role="group">
                                <button wire:click="openModal({{ $quote->id }})" class="btn btn-sm btn-primary rounded-3 me-2">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                                <button wire:click="deleteQuote({{ $quote->id }})" class="btn btn-sm btn-danger rounded-3" wire:confirm="Are you sure? You want to delete this quote request?">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3 d-block"></i>
                                <h5 class="text-muted">No quote requests found</h5>
                                <p class="text-muted">When customers request quotes from pricing page, they will appear here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="card-body">
            <!-- Pagination -->
            <div class="mt-2">
                {{ $this->quotes->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal for View/Update Quote Request -->
    @if($showModal && $selectedQuote)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice-dollar me-2"></i> Quote Request Details #{{ $selectedQuote->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Customer Information -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h6 class="fw-bold border-bottom pb-2 mb-3">
                                <i class="fas fa-user-circle me-2 text-primary"></i> Customer Information
                            </h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Full Name</label>
                            <p class="mb-0 fw-medium">{{ $selectedQuote->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Email Address</label>
                            <p class="mb-0">
                                <a href="mailto:{{ $selectedQuote->email }}" class="text-decoration-none">
                                    {{ $selectedQuote->email }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Phone Number</label>
                            <p class="mb-0">{{ $selectedQuote->phone ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Requested Plan</label>
                            <p class="mb-0">
                                <span class="badge bg-primary">{{ $selectedQuote->plan_name ?? 'Custom Quote' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Request Date</label>
                            <p class="mb-0">{{ $selectedQuote->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">IP Address</label>
                            <p class="mb-0"><code>{{ $selectedQuote->ip_address ?? 'Not recorded' }}</code></p>
                        </div>
                    </div>
                    
                    <!-- Customer Message -->
                    <div class="mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">
                            <i class="fas fa-comment-dots me-2 text-primary"></i> Customer Message
                        </h6>
                        <div class="p-3 bg-light rounded-3">
                            <p class="mb-0">{{ $selectedQuote->message }}</p>
                        </div>
                    </div>
                    
                    <!-- Admin Section -->
                    <h6 class="fw-bold border-bottom pb-2 mb-3">
                        <i class="fas fa-user-shield me-2 text-primary"></i> Admin Actions
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select rounded-3" wire:model="status">
                                <option value="pending">🟡 Pending</option>
                                <option value="contacted">🔵 Contacted</option>
                                <option value="quoted">💲 Quoted</option>
                                <option value="converted">🟢 Converted</option>
                                <option value="rejected">🔴 Rejected</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Quoted Amount ($)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control rounded-3" wire:model="quoted_amount" placeholder="Enter quoted amount">
                            </div>
                            <small class="text-muted">Enter the final price quoted to customer</small>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Internal Admin Notes</label>
                            <textarea class="form-control rounded-3" wire:model="admin_notes" rows="3" placeholder="Add private notes about this quote request..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Quick Reply Template -->
                    <div class="mt-3">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">
                            <i class="fas fa-reply-all me-2 text-primary"></i> Quick Reply Templates
                        </h6>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="document.querySelector('[wire\\:model=\'admin_notes\']').value += 'Thank you for your interest. We will review and get back to you soon.'">
                                    <i class="fas fa-smile me-1"></i> Standard Reply
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-outline-success w-100" onclick="document.querySelector('[wire\\:model=\'quoted_amount\']').value = '500'; document.querySelector('[wire\\:model=\'status\']').value = 'quoted';">
                                    <i class="fas fa-dollar-sign me-1"></i> Quick Quote ($500)
                                </button>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="document.querySelector('[wire\\:model=\'status\']').value = 'rejected';">
                                    <i class="fas fa-times me-1"></i> Mark Rejected
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" wire:click="closeModal">
                        <i class="fas fa-times me-2"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary rounded-3" wire:click="updateQuote">
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
    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #FE171B, #cc1215);
        border-color: transparent;
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .input-group-text {
        background: white;
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
    table td {
        vertical-align: middle;
    }
    .badge {
        font-weight: 500;
    }
    .btn-sm {
        font-size: 0.8rem;
    }
</style>
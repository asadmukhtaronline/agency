<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ContactInquiry;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status_filter = '';
    public $perPage = 10;
    public $showModal = false;
    public $selectedContact = null;
    public $admin_notes = '';
    public $reply_message = '';
    public $status = '';
    
    public function openModal($id)
    {
        $this->selectedContact = ContactInquiry::find($id);
        $this->admin_notes = $this->selectedContact->admin_notes;
        $this->reply_message = $this->selectedContact->reply_message;
        $this->status = $this->selectedContact->status;
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedContact = null;
        $this->admin_notes = '';
        $this->reply_message = '';
        $this->status = '';
    }
    
    public function updateContact()
    {
        $contact = ContactInquiry::find($this->selectedContact->id);
        
        $contact->update([
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'reply_message' => $this->reply_message,
            'replied_at' => $this->status == 'replied' ? now() : $contact->replied_at
        ]);
        
        $this->closeModal();
        session()->flash('message', 'Contact inquiry updated successfully!');
    }
    
    public function deleteContact($id)
    {
        ContactInquiry::find($id)->delete();
        session()->flash('message', 'Contact inquiry deleted successfully!');
    }
    
    public function getContactsProperty()
    {
        $query = ContactInquiry::query();
        
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('subject', 'like', '%'.$this->search.'%');
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
            <h2 class="fw-bold mb-0">Contact Inquiries</h2>
            <p class="text-muted">Manage customer contact form submissions</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-warning rounded-pill px-3 py-2">
                <i class="fas fa-clock me-1"></i> Pending: {{ App\Models\ContactInquiry::where('status', 'pending')->count() }}
            </span>
            <span class="badge bg-success rounded-pill px-3 py-2">
                <i class="fas fa-check me-1"></i> Resolved: {{ App\Models\ContactInquiry::where('status', 'resolved')->count() }}
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
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by name, email or subject...">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="replied">Replied</option>
                        <option value="resolved">Resolved</option>
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
        
        <!-- Contacts Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-4"><i class="fas fa-user text-primary me-2"></i> Customer</th>
                        <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Subject</th>
                        <th class="py-3 px-4"><i class="fas fa-clock text-primary me-2"></i> Date</th>
                        <th class="py-3 px-4"><i class="fas fa-info-circle text-primary me-2"></i> Status</th>
                        <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->contacts as $contact)
                    <tr>
                        <td class="px-4">
                            <strong>{{ $contact->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $contact->email }}</small>
                            @if($contact->phone)
                                <br><small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $contact->phone }}</small>
                            @endif
                        </td>
                        <td class="px-4">
                            <span class="fw-semibold">{{ $contact->subject ?? 'General Inquiry' }}</span>
                            <br>
                            <small class="text-muted">{{ Str::limit($contact->message, 50) }}</small>
                        </td>
                        <td class="px-4">
                            <small>{{ $contact->created_at->format('M d, Y h:i A') }}</small>
                            <br>
                            <small class="text-muted">{{ $contact->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="px-4">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'replied' => 'info',
                                    'resolved' => 'success'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$contact->status] ?? 'secondary' }} rounded-pill px-3 py-2">
                                <i class="fas {{ $contact->status == 'pending' ? 'fa-clock' : ($contact->status == 'replied' ? 'fa-reply' : 'fa-check-circle') }} me-1"></i>
                                {{ ucfirst($contact->status) }}
                            </span>
                        </td>
                        <td class="px-4 text-center">
                            <div class="btn-group" role="group">
                                <button wire:click="openModal({{ $contact->id }})" class="btn btn-sm btn-primary rounded-3 me-2">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                                <button wire:click="deleteContact({{ $contact->id }})" class="btn btn-sm btn-danger rounded-3" wire:confirm="Are you sure? You want to delete this inquiry?">
                                    <i class="fas fa-trash-alt me-1"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-envelope-open-text fa-3x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No contact inquiries found</h5>
                            <p class="text-muted">When customers submit the contact form, they will appear here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="card-body">
            <!-- Pagination -->
            <div class="mt-2">
                {{ $this->contacts->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal for View/Update Contact Inquiry -->
    @if($showModal && $selectedContact)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope me-2"></i> Contact Inquiry Details
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
                            <p class="mb-0 fw-medium">{{ $selectedContact->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Email Address</label>
                            <p class="mb-0">
                                <a href="mailto:{{ $selectedContact->email }}" class="text-decoration-none">
                                    {{ $selectedContact->email }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Phone Number</label>
                            <p class="mb-0">{{ $selectedContact->phone ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Subject</label>
                            <p class="mb-0">
                                <span class="badge bg-secondary">{{ $selectedContact->subject ?? 'General Inquiry' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">Submitted On</label>
                            <p class="mb-0">{{ $selectedContact->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted small">IP Address</label>
                            <p class="mb-0"><code>{{ $selectedContact->ip_address ?? 'Not recorded' }}</code></p>
                        </div>
                    </div>
                    
                    <!-- Customer Message -->
                    <div class="mb-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3">
                            <i class="fas fa-comment-dots me-2 text-primary"></i> Customer Message
                        </h6>
                        <div class="p-3 bg-light rounded-3">
                            <p class="mb-0">{{ $selectedContact->message }}</p>
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
                                <option value="replied">🔵 Replied</option>
                                <option value="resolved">🟢 Resolved</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Reply Message (Will be sent to customer)</label>
                            <textarea class="form-control rounded-3" wire:model="reply_message" rows="4" placeholder="Type your reply here..."></textarea>
                            <small class="text-muted">You can copy this reply to send via email</small>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label class="form-label fw-semibold">Internal Admin Notes</label>
                            <textarea class="form-control rounded-3" wire:model="admin_notes" rows="2" placeholder="Add private notes for reference..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" wire:click="closeModal">
                        <i class="fas fa-times me-2"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary rounded-3" wire:click="updateContact">
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
    table td {
        vertical-align: middle;
    }
    .badge {
        font-weight: 500;
    }
</style>
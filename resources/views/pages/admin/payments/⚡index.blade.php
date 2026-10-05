<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProjectPayment;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $payment_method_filter = '';
    public $status_filter = '';
    public $perPage = 10;
    
    public function deletePayment($id)
    {
        $payment = ProjectPayment::find($id);
        if ($payment) {
            // Delete invoice PDF if exists
            if ($payment->invoice_pdf && \Storage::disk('public')->exists($payment->invoice_pdf)) {
                \Storage::disk('public')->delete($payment->invoice_pdf);
            }
            $payment->delete();
            session()->flash('message', 'Payment deleted successfully!');
        }
    }
    
    public function getPaymentsProperty()
    {
        $query = ProjectPayment::with(['project.client', 'milestone', 'paymentGateway']);
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('payment_number', 'like', '%'.$this->search.'%')
                  ->orWhere('transaction_id', 'like', '%'.$this->search.'%')
                  ->orWhereHas('project', function($pq) {
                      $pq->where('name', 'like', '%'.$this->search.'%');
                  });
            });
        }
        
        if ($this->payment_method_filter) {
            $query->where('payment_method', $this->payment_method_filter);
        }
        
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        
        return $query->latest()->paginate($this->perPage);
    }
    
    public function getPaymentMethodsProperty()
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'jazzcash' => 'JazzCash',
            'easypaisa' => 'EasyPaisa'
        ];
    }
    
    public function getStatusesProperty()
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed'
        ];
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
            <h2 class="fw-bold mb-0">Payments Management</h2>
            <p class="text-muted">Manage all project payments and invoices</p>
        </div>
        <a href="{{ route('admin.payments.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Record Payment
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
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by payment #, transaction ID...">
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select rounded-3" wire:model.live="payment_method_filter">
                        <option value="">All Payment Methods</option>
                        <option value="cash">Cash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="paypal">PayPal</option>
                        <option value="stripe">Stripe</option>
                        <option value="jazzcash">JazzCash</option>
                        <option value="easypaisa">EasyPaisa</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
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
            
            <!-- Payments Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-receipt text-primary me-2"></i></th>
                            <th class="py-3 px-4"><i class="fas fa-project-diagram text-primary me-2"></i> Project</th>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Milestone</th>
                            <th class="py-3 px-4"><i class="fas fa-credit-card text-primary me-2"></i> Method</th>
                            <th class="py-3 px-4"><i class="fas fa-calendar text-primary me-2"></i> Date</th>
                            <th class="py-3 px-4"><i class="fas fa-info-circle text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->payments as $payment)
                        <tr>
                            <td class="px-4">
                                <code>{{ $payment->payment_number }}</code>
                                @if($payment->transaction_id)
                                    <br><small class="text-muted">Txn: {{ $payment->transaction_id }}</small>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ Str::limit($payment->project->name, 30) }}</strong>
                                <br>
                                <small class="text-muted">{{ $payment->project->client->name }}</small>
                            </td>
                            <td class="px-4">
                                @if($payment->milestone)
                                    {{ $payment->milestone->milestone_name }}
                                @else
                                    <span class="badge bg-secondary">Direct Payment</span>
                                @endif
                            </td>
                            <td class="px-4">
                                @php
                                    $methodIcons = [
                                        'cash' => 'fa-money-bill-wave',
                                        'bank_transfer' => 'fa-university',
                                        'credit_card' => 'fa-credit-card',
                                        'paypal' => 'fab fa-paypal',
                                        'stripe' => 'fab fa-stripe',
                                        'jazzcash' => 'fa-mobile-alt',
                                        'easypaisa' => 'fa-mobile-alt'
                                    ];
                                @endphp
                                <i class="{{ $methodIcons[$payment->payment_method] ?? 'fa-credit-card' }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                            </td>
                            <td class="px-4">
                                {{ $payment->payment_date->format('M d, Y') }}
                            </td>
                            <td class="px-4">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'completed' => 'success',
                                        'failed' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$payment->status] }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-success rounded-3 me-2" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($payment->invoice_pdf)
                                        <a href="{{ asset('storage/' . $payment->invoice_pdf) }}" target="_blank" class="btn btn-sm btn-info rounded-3 me-2" title="Download Invoice">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    @endif
                                    <button wire:click="deletePayment({{ $payment->id }})" class="btn btn-sm btn-danger rounded-3" title="Delete" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No payments found</h5>
                                    <a href="{{ route('admin.payments.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Record First Payment
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->payments->links() }}
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
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .input-group-text {
        border-right: none;
    }
    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }
    code {
        font-size: 12px;
    }
</style>
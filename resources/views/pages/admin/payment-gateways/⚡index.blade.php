<?php
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PaymentGateway;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    
    public function deleteGateway($id)
    {
        $gateway = PaymentGateway::find($id);
        if ($gateway) {
            $gateway->delete();
            session()->flash('message', 'Payment gateway deleted successfully!');
        }
    }
    
    public function toggleStatus($id)
    {
        $gateway = PaymentGateway::find($id);
        $gateway->is_active = !$gateway->is_active;
        $gateway->save();
        session()->flash('message', 'Gateway status updated!');
    }
    
    public function getGatewaysProperty()
    {
        return PaymentGateway::where('name', 'like', '%'.$this->search.'%')
            ->orderBy('order')
            ->paginate($this->perPage);
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
            <h2 class="fw-bold mb-0">Payment Gateways</h2>
            <p class="text-muted">Manage payment methods for receiving payments</p>
        </div>
        <a href="{{ route('admin.payment-gateways.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add Gateway
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Search -->
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search gateways...">
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select wire:model.live="perPage" class="form-select rounded-3">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
            </div>
            
            <!-- Gateways Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Icon</th>
                            <th class="py-3 px-4">Gateway Name</th>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Order</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->gateways as $gateway)
                        <tr>
                            <td class="px-4">{{ $gateway->id }}</td>
                            <td class="px-4">
                                @if($gateway->icon)
                                    <i class="{{ $gateway->icon }} fa-2x text-primary"></i>
                                @else
                                    <i class="fas fa-credit-card fa-2x text-muted"></i>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $gateway->name }}</strong>
                            </td>
                            <td class="px-4">
                                <code>{{ $gateway->code }}</code>
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $gateway->id }})" {{ $gateway->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4">{{ $gateway->order }}</td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.payment-gateways.edit', $gateway->id) }}" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <button wire:click="deleteGateway({{ $gateway->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-credit-card fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No payment gateways found</h5>
                                    <a href="{{ route('admin.payment-gateways.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Gateway
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->gateways->links() }}
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
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .input-group-text {
        border-right: none;
    }
</style>
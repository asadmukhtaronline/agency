<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    
    public function deleteProduct($id)
    {
        $product = Product::find($id);
        if ($product) {
            $product->delete();
            session()->flash('message', 'Product deleted successfully!');
        }
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function getProductsProperty()
    {
        return Product::where('name', 'like', '%'.$this->search.'%')
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
            <h2 class="fw-bold mb-0">Products Management</h2>
            <p class="text-muted">Manage your products from here</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Product
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Search and Per Page -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search products...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select rounded-3">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-image text-primary me-2"></i> Image</th>
                            <th class="py-3 px-4"><i class="fas fa-box text-primary me-2"></i> Name</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4"><i class="fas fa-sort-numeric-down text-primary me-2"></i> Order</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->products as $product)
                        <tr>
                            <td class="px-4">
                                @if($product->image)
                                    <img src="{{ asset('storage/'.$product->image) }}" width="50" height="50" class="rounded-3" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-3 d-inline-flex p-2">
                                        <i class="fas fa-box fa-lg text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $product->name }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($product->short_description, 50) }}</small>
                            </td>
                            <td class="px-4">
                                <span class="badge rounded-pill px-3 py-2 {{ $product->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas {{ $product->is_active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4">{{ $product->order }}</td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group"> 
                                    <a href="{{ route('admin.product-categories.show', $product->id) }}" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-eye me-1"></i> 
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> 
                                    </a>
                                    <button wire:click="deleteProduct({{ $product->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No products found</h5>
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Product
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->products->links() }}
            </div>
        </div>
    </div>
</div>

<style>
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
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
    }
    .btn-group .btn {
        transition: all 0.3s ease;
    }
    .btn-group .btn:hover {
        transform: translateY(-2px);
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .rounded-4 {
        border-radius: 20px;
    }
</style>
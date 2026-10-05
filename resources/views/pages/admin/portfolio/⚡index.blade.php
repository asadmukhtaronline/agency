<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PortfolioItem;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $category_filter = '';
    public $status_filter = '';
    public $perPage = 10;
    
    public function deleteItem($id)
    {
        $item = PortfolioItem::find($id);
        if ($item) {
            // Delete featured image
            if ($item->featured_image && \Storage::disk('public')->exists($item->featured_image)) {
                \Storage::disk('public')->delete($item->featured_image);
            }
            // Delete gallery images
            $gallery = json_decode($item->gallery, true) ?? [];
            foreach ($gallery as $img) {
                if (\Storage::disk('public')->exists($img)) {
                    \Storage::disk('public')->delete($img);
                }
            }
            $item->delete();
            session()->flash('message', 'Portfolio item deleted successfully!');
        }
    }
    
    public function toggleStatus($id)
    {
        $item = PortfolioItem::find($id);
        $item->is_active = !$item->is_active;
        $item->save();
        session()->flash('message', 'Status updated successfully!');
    }
    
    public function toggleFeatured($id)
    {
        $item = PortfolioItem::find($id);
        $item->is_featured = !$item->is_featured;
        $item->save();
        session()->flash('message', 'Featured status updated!');
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function getPortfolioItemsProperty()
    {
        $query = PortfolioItem::query();
        
        if ($this->search) {
            $query->where('title', 'like', '%'.$this->search.'%')
                  ->orWhere('category', 'like', '%'.$this->search.'%')
                  ->orWhere('client_name', 'like', '%'.$this->search.'%');
        }
        
        if ($this->category_filter) {
            $query->where('category', $this->category_filter);
        }
        
        if ($this->status_filter !== '') {
            $query->where('is_active', $this->status_filter);
        }
        
        return $query->orderBy('order')->paginate($this->perPage);
    }
    
    public function getCategoriesProperty()
    {
        return PortfolioItem::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
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
            <h2 class="fw-bold mb-0">Portfolio Management</h2>
            <p class="text-muted">Manage your portfolio projects</p>
        </div>
        <a href="{{ route('admin.portfolio.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Project
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
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by title, category or client...">
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select rounded-3" wire:model.live="category_filter">
                        <option value="">All Categories</option>
                        @foreach($this->categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
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
            
            <!-- Portfolio Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-image text-primary me-2"></i> </th>
                            <th class="py-3 px-4"><i class="fas fa-heading text-primary me-2"></i> Title</th>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Category</th>
                            <th class="py-3 px-4"><i class="fas fa-user text-primary me-2"></i> Client</th>
                            <th class="py-3 px-4"><i class="fas fa-star text-primary me-2"></i> Featured</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->portfolioItems as $item)
                        <tr>
                            <td class="px-4">
                                @if($item->featured_image)
                                    <img src="{{ asset('storage/' . $item->featured_image) }}" width="50" height="50" class="rounded-3" style="object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-3 d-inline-flex p-2">
                                        <i class="fas fa-image fa-lg text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $item->title }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                            </td>
                            <td class="px-4">
                                <span class="badge bg-secondary">{{ $item->category }}</span>
                            </td>
                            <td class="px-4">{{ $item->client_name ?? '—' }}</td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleFeatured({{ $item->id }})" {{ $item->is_featured ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $item->id }})" {{ $item->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.portfolio.view', $item->id) }}" class="btn btn-sm btn-success rounded-3 me-2">
                                        <i class="fas fa-eye me-1"></i> 
                                    </a>
                                    <a href="{{ route('admin.portfolio.edit', $item->id) }}" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> 
                                    </a>
                                    <button wire:click="deleteItem({{ $item->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure you want to delete this item?')">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-briefcase fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No portfolio items found</h5>
                                    <a href="{{ route('admin.portfolio.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Project
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->portfolioItems->links() }}
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
<?php

use Livewire\Component;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

new class extends Component
{
    public $name = '';
    public $slug = '';
    public $icon = '';
    public $is_active = true;
    public $editId = null;
    public $showModal = false;
    public $search = '';
    
    public function openModal()
    {
        $this->reset(['name', 'slug', 'icon', 'is_active', 'editId']);
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'slug', 'icon', 'is_active', 'editId']);
    }
    
    public function edit($id)
    {
        $category = ProductCategory::find($id);
        $this->editId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->icon = $category->icon;
        $this->is_active = $category->is_active;
        $this->showModal = true;
    }
    
    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }
    
    public function save()
    {
        $this->validate([
            'name' => 'required|min:2|max:255',
            'slug' => 'required|unique:product_categories,slug,' . $this->editId,
            'icon' => 'nullable|max:100',
        ]);
        
        if ($this->editId) {
            $category = ProductCategory::find($this->editId);
            $category->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Category updated successfully!');
        } else {
            ProductCategory::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Category created successfully!');
        }
        
        $this->closeModal();
    }
    
    public function deleteCategory($id)
    {
        $category = ProductCategory::find($id);
        if ($category && $category->products()->count() == 0) {
            $category->delete();
            session()->flash('message', 'Category deleted successfully!');
        } else {
            session()->flash('error', 'Cannot delete category with products!');
        }
    }
    
    public function getCategoriesProperty()
    {
        return ProductCategory::where('name', 'like', '%'.$this->search.'%')
            ->orderBy('name')
            ->get();
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
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Product Categories</h2>
            <p class="text-muted">Manage your product categories from here</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Category
        </button>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Search -->
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-3">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search categories...">
                </div>
            </div>
            
            <!-- Categories Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-hashtag text-primary me-2"></i> ID</th>
                            <th class="py-3 px-4"><i class="fas fa-icons text-primary me-2"></i> Icon</th>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Name</th>
                            <th class="py-3 px-4"><i class="fas fa-link text-primary me-2"></i> Slug</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->categories as $category)
                        <tr>
                            <td class="px-4">{{ $category->id }}</td>
                            <td class="px-4">
                                @if($category->icon)
                                    <div class="icon-wrapper bg-light rounded-3 d-inline-flex p-2">
                                        <i class="{{ $category->icon }} fa-lg text-primary"></i>
                                    </div>
                                @else
                                    <div class="icon-wrapper bg-light rounded-3 d-inline-flex p-2">
                                        <i class="fas fa-tag fa-lg text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $category->name }}</strong>
                            </td>
                            <td class="px-4">
                                <code>{{ $category->slug }}</code>
                            </td>
                            <td class="px-4">
                                <span class="badge rounded-pill px-3 py-2 {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    <i class="fas {{ $category->is_active ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="edit({{ $category->id }})" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </button>
                                    <button wire:click="deleteCategory({{ $category->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-folder-open fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No categories found</h5>
                                    <button wire:click="openModal" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Category
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal for Create/Edit Category -->
    @if($showModal)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas {{ $editId ? 'fa-edit' : 'fa-plus-circle' }} me-2"></i>
                        {{ $editId ? 'Edit Category' : 'Create New Category' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('slug') is-invalid @enderror" wire:model="slug">
                        @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">Auto-generated from name</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Icon Class</label>
                        <input type="text" class="form-control rounded-3" wire:model="icon" placeholder="fa-solid fa-tag">
                        @if($icon)
                            <div class="mt-2">
                                <span class="badge bg-light text-dark p-2 rounded-3">
                                    Preview: <i class="{{ $icon }} ms-1"></i>
                                </span>
                            </div>
                        @endif
                        <small class="text-muted">Font Awesome icon class (optional)</small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">Active (Visible on website)</label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" wire:click="closeModal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3" wire:click="save">
                            <i class="fas fa-save me-2"></i> {{ $editId ? 'Update' : 'Save' }} Category
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .icon-wrapper {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
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
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .rounded-4 {
        border-radius: 20px;
    }
</style>
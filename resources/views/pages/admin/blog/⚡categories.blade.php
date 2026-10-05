<?php

use Livewire\Component;
use App\Models\BlogCategory;
use Livewire\WithPagination;
use Illuminate\Support\Str;

new class extends Component
{
    use WithPagination;
    
    public $showModal = false;
    public $editId = null;
    
    // Form fields
    public $name = '';
    public $slug = '';
    public $color = '#FE171B';
    public $description = '';
    public $is_active = true;
    
    public $search = '';
    
    public function updatedName($value)
    {
        // Auto generate slug from name
        $this->slug = Str::slug($value);
    }
    
    public function openModal()
    {
        $this->reset(['name', 'slug', 'color', 'description', 'is_active', 'editId']);
        $this->resetValidation();
        $this->color = '#FE171B';
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'slug', 'color', 'description', 'is_active', 'editId']);
        $this->resetValidation();
    }
    
    public function edit($id)
    {
        $category = BlogCategory::find($id);
        $this->editId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->color = $category->color;
        $this->description = $category->description;
        $this->is_active = $category->is_active;
        $this->showModal = true;
    }
    
    public function save()
    {
        // Validation rules
        $rules = [
            'name' => 'required|min:2|max:100',
            'slug' => 'required|max:100',
            'color' => 'nullable|max:20',
            'description' => 'nullable|max:500',
            'is_active' => 'boolean'
        ];
        
        // Add unique validation with exception for current record
        if ($this->editId) {
            $rules['name'] .= '|unique:blog_categories,name,' . $this->editId;
            $rules['slug'] .= '|unique:blog_categories,slug,' . $this->editId;
        } else {
            $rules['name'] .= '|unique:blog_categories,name';
            $rules['slug'] .= '|unique:blog_categories,slug';
        }
        
        $this->validate($rules);
        
        if ($this->editId) {
            $category = BlogCategory::find($this->editId);
            $category->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'color' => $this->color,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Category updated successfully!');
        } else {
            BlogCategory::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'color' => $this->color,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Category created successfully!');
        }
        
        $this->closeModal();
    }
    
    public function deleteCategory($id)
    {
        $category = BlogCategory::find($id);
        
        // Check if category has any blog posts
        if ($category && $category->blogPosts()->count() > 0) {
            session()->flash('error', 'Cannot delete category! There are ' . $category->blogPosts()->count() . ' blog post(s) associated with this category.');
            return;
        }
        
        // Delete category
        $category->delete();
        session()->flash('message', 'Category deleted successfully!');
    }
    
    public function toggleStatus($id)
    {
        $category = BlogCategory::find($id);
        $category->is_active = !$category->is_active;
        $category->save();
        session()->flash('message', 'Category status updated!');
    }
    
    // Computed property for categories (like getCountriesProperty in your reference)
    public function getCategoriesProperty()
    {
        return BlogCategory::where('name', 'like', '%'.$this->search.'%')
            ->orWhere('slug', 'like', '%'.$this->search.'%')
            ->orderBy('name')
            ->paginate(10);
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
            <h2 class="fw-bold mb-0">Blog Categories</h2>
            <p class="text-muted">Manage your blog categories</p>
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
                    <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by category name or slug...">
                </div>
            </div>
            
            <!-- Categories Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Name</th>
                            <th class="py-3 px-4"><i class="fas fa-link text-primary me-2"></i> Slug</th>
                            <th class="py-3 px-4"><i class="fas fa-palette text-primary me-2"></i> Color</th>
                            <th class="py-3 px-4"><i class="fas fa-align-left text-primary me-2"></i> Description</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4"><i class="fas fa-chart-line text-primary me-2"></i> Posts Count</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->categories as $category)
                        <tr>
                            <td class="px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle" style="width: 12px; height: 12px; background-color: {{ $category->color }};"></span>
                                    <strong>{{ $category->name }}</strong>
                                </div>
                            </td>
                            <td class="px-4">
                                <code>{{ $category->slug }}</code>
                            </td>
                            <td class="px-4">
                                <span class="badge" style="background-color: {{ $category->color }}; color: white;">
                                    {{ $category->color }}
                                </span>
                            </td>
                            <td class="px-4">
                                {{ Str::limit($category->description, 50) }}
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $category->id }})" {{ $category->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4">
                                <span class="badge bg-info rounded-pill">
                                    <i class="fas fa-file-alt me-1"></i> {{ $category->blogPosts()->count() }} posts
                                </span>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="edit({{ $category->id }})" class="btn btn-sm btn-info rounded-3 me-2" title="Edit Category">
                                        <i class="fas fa-edit me-1"></i> 
                                    </button>
                                    <button wire:click="deleteCategory({{ $category->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure you want to delete this category?')" title="Delete Category">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
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
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $this->categories->links() }}
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
                        {{ $editId ? 'Edit Category' : 'Add New Category' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g., Technology">
                        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('slug') is-invalid @enderror" wire:model="slug" placeholder="e.g., technology">
                        @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">URL-friendly version of the name</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Color</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" class="form-control form-control-color w-25 rounded-3" wire:model="color">
                            <input type="text" class="form-control rounded-3" wire:model="color" placeholder="#FE171B">
                        </div>
                        @error('color') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">Choose a color for the category badge</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control rounded-3 @error('description') is-invalid @enderror" wire:model="description" rows="3" placeholder="Brief description of this category"></textarea>
                        @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">
                                <i class="fas fa-toggle-on me-1"></i> Active (Visible on website)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4 py-2 rounded-3" wire:click="save">
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
    }
    .btn-info {
        background: #0dcaf0;
        border: none;
        transition: transform 0.2s;
    }
    .btn-info:hover {
        background: #0bb5d8;
        transform: translateY(-1px);
    }
    .btn-danger {
        transition: transform 0.2s;
    }
    .btn-danger:hover {
        transform: translateY(-1px);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
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
    .btn-sm {
        padding: 0.4rem 0.8rem;
    }
</style>
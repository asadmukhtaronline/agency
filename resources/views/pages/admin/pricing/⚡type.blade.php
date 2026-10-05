<?php

use Livewire\Component;
use App\Models\ServiceType;
use Illuminate\Support\Str;

new class extends Component
{
    public $showModal = false;
    public $editId = null;
    
    // Form fields
    public $name = '';
    public $slug = '';
    public $icon = '';
    public $description = '';
    public $is_active = true;
    public $order = 0;
    
    public $search = '';
    
    public function openModal()
    {
        $this->reset(['name', 'slug', 'icon', 'description', 'is_active', 'order', 'editId']);
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'slug', 'icon', 'description', 'is_active', 'order', 'editId']);
    }
    
    public function edit($id)
    {
        $type = ServiceType::find($id);
        $this->editId = $type->id;
        $this->name = $type->name;
        $this->slug = $type->slug;
        $this->icon = $type->icon;
        $this->description = $type->description;
        $this->is_active = $type->is_active;
        $this->order = $type->order;
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
            'slug' => 'required|unique:service_types,slug,' . $this->editId,
            'icon' => 'nullable|max:100',
            'description' => 'nullable|max:500',
            'is_active' => 'boolean',
            'order' => 'integer'
        ]);
        
        if ($this->editId) {
            $type = ServiceType::find($this->editId);
            $type->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'order' => $this->order,
            ]);
            session()->flash('message', 'Service type updated successfully!');
        } else {
            ServiceType::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon,
                'description' => $this->description,
                'is_active' => $this->is_active,
                'order' => $this->order,
            ]);
            session()->flash('message', 'Service type created successfully!');
        }
        
        $this->closeModal();
    }
    
    public function deleteType($id)
    {
        $type = ServiceType::find($id);
        if ($type && $type->pricingPlans()->count() == 0) {
            $type->delete();
            session()->flash('message', 'Service type deleted successfully!');
        } else {
            session()->flash('error', 'Cannot delete service type with existing pricing plans!');
        }
    }
    
    public function toggleStatus($id)
    {
        $type = ServiceType::find($id);
        $type->is_active = !$type->is_active;
        $type->save();
        session()->flash('message', 'Service type status updated!');
    }
    
    public function getTypesProperty()
    {
        return ServiceType::where('name', 'like', '%'.$this->search.'%')
            ->orderBy('order')
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
            <h2 class="fw-bold mb-0">Service Types Management</h2>
            <p class="text-muted">Manage service categories for pricing plans</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Service Type
        </button>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Search -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search service types...">
                    </div>
                </div>
            </div>
            
            <!-- Service Types Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-hashtag text-primary me-2"></i> ID</th>
                            <th class="py-3 px-4"><i class="fas fa-icons text-primary me-2"></i> Icon</th>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Name</th>
                            <th class="py-3 px-4"><i class="fas fa-link text-primary me-2"></i> Slug</th>
                            <th class="py-3 px-4"><i class="fas fa-align-left text-primary me-2"></i> Description</th>
                            <th class="py-3 px-4"><i class="fas fa-sort-numeric-down text-primary me-2"></i> Order</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->types as $type)
                        <tr>
                            <td class="px-4">{{ $type->id }}</td>
                            <td class="px-4">
                                @if($type->icon)
                                    <div class="icon-wrapper bg-light rounded-3 d-inline-flex p-2">
                                        <i class="{{ $type->icon }} fa-lg text-primary"></i>
                                    </div>
                                @else
                                    <div class="icon-wrapper bg-light rounded-3 d-inline-flex p-2">
                                        <i class="fas fa-tag fa-lg text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $type->name }}</strong>
                            </td>
                            <td class="px-4">
                                <code>{{ $type->slug }}</code>
                            </td>
                            <td class="px-4">
                                {{ Str::limit($type->description, 50) }}
                            </td>
                            <td class="px-4">{{ $type->order }}</td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $type->id }})" {{ $type->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="edit({{ $type->id }})" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> 
                                    </button>
                                    <button wire:click="deleteType({{ $type->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-tags fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No service types found</h5>
                                    <button wire:click="openModal" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Service Type
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->types->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal for Create/Edit Service Type -->
    @if($showModal)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas {{ $editId ? 'fa-edit' : 'fa-plus-circle' }} me-2"></i>
                        {{ $editId ? 'Edit Service Type' : 'Add New Service Type' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Service Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g., WordPress Maintenance">
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
                        <input type="text" class="form-control rounded-3" wire:model="icon" placeholder="fab fa-wordpress">
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
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control rounded-3" wire:model="description" rows="3" placeholder="Brief description of this service type"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                                <label class="form-check-label fw-semibold" for="isActive">Active (Visible on website)</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4 py-2 rounded-3" wire:click="save">
                            <i class="fas fa-save me-2"></i> {{ $editId ? 'Update' : 'Save' }} Service Type
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
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
    }
</style>
<?php

use Livewire\Component;
use App\Models\ClientType;
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
    public $icon = '';
    public $color = '#FE171B';
    public $description = '';
    public $order = 0;
    public $is_active = true;
    
    public $search = '';
    public $perPage = 10;
    
    protected $queryString = ['search'];
    
    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }
    
    public function openModal()
    {
        $this->reset(['name', 'slug', 'icon', 'color', 'description', 'order', 'is_active', 'editId']);
        $this->resetValidation();
        $this->color = '#FE171B';
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'slug', 'icon', 'color', 'description', 'order', 'is_active', 'editId']);
        $this->resetValidation();
    }
    
    public function edit($id)
    {
        $type = ClientType::find($id);
        $this->editId = $type->id;
        $this->name = $type->name;
        $this->slug = $type->slug;
        $this->icon = $type->icon;
        $this->color = $type->color;
        $this->description = $type->description;
        $this->order = $type->order;
        $this->is_active = $type->is_active;
        $this->showModal = true;
    }
    
    public function save()
    {
        $rules = [
            'name' => 'required|min:2|max:100',
            'slug' => 'required|max:100',
            'icon' => 'nullable|max:50',
            'color' => 'nullable|max:20',
            'description' => 'nullable|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ];
        
        if ($this->editId) {
            $rules['slug'] .= '|unique:client_types,slug,' . $this->editId;
            $rules['name'] .= '|unique:client_types,name,' . $this->editId;
        } else {
            $rules['slug'] .= '|unique:client_types,slug';
            $rules['name'] .= '|unique:client_types,name';
        }
        
        $this->validate($rules);
        
        if ($this->editId) {
            $type = ClientType::find($this->editId);
            $type->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon,
                'color' => $this->color,
                'description' => $this->description,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Client Type updated successfully!');
        } else {
            ClientType::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => $this->icon,
                'color' => $this->color,
                'description' => $this->description,
                'order' => $this->order,
                'is_active' => $this->is_active,
            ]);
            session()->flash('message', 'Client Type created successfully!');
        }
        
        $this->closeModal();
    }
    
    public function deleteType($id)
    {
        $type = ClientType::find($id);
        $type->delete();
        session()->flash('message', 'Client Type deleted successfully!');
    }
    
    public function toggleStatus($id)
    {
        $type = ClientType::find($id);
        $type->is_active = !$type->is_active;
        $type->save();
        session()->flash('message', 'Client Type status updated!');
    }
    
    public function getTypesProperty()
    {
        return ClientType::where('name', 'like', '%'.$this->search.'%')
            ->orWhere('slug', 'like', '%'.$this->search.'%')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
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
            <h2 class="fw-bold mb-0">Client Types</h2>
            <p class="text-muted">Manage client business types (Construction, Law, Healthcare, etc.)</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Client Type
        </button>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Search and Per Page -->
            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by name or slug...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select wire:model.live="perPage" class="form-select rounded-3">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
            
            <!-- Types Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Icon</th>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Slug</th>
                            <th class="py-3 px-4">Color</th>
                            <th class="py-3 px-4">Order</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->types as $type)
                        <tr>
                            <td class="px-4">{{ $type->id }}</td>
                            <td class="px-4">
                                @if($type->icon)
                                    <i class="{{ $type->icon }} fa-2x"></i>
                                @else
                                    <i class="fas fa-building fa-2x text-muted"></i>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $type->name }}</strong>
                                @if($type->description)
                                    <br><small class="text-muted">{{ Str::limit($type->description, 40) }}</small>
                                @endif
                            </td>
                            <td class="px-4">
                                <code>{{ $type->slug }}</code>
                            </td>
                            <td class="px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle" style="width: 20px; height: 20px; background-color: {{ $type->color }};"></span>
                                    <span>{{ $type->color }}</span>
                                </div>
                            </td>
                            <td class="px-4">
                                {{ $type->order }}
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $type->id }})" {{ $type->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.crm.type.show',$type->id) }}">
                                    <button wire:click="edit({{ $type->id }})" class="btn btn-sm btn-warning rounded-3 me-2" title="Edit">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    </a>
                                    <button wire:click="edit({{ $type->id }})" class="btn btn-sm btn-info rounded-3 me-2" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="deleteType({{ $type->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure you want to delete this client type?')" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-building fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No client types found</h5>
                                    <button wire:click="openModal" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Client Type
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $this->types->links() }}
            </div>
        </div>
    </div>
    
    <!-- Modal for Create/Edit -->
    @if($showModal)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas {{ $editId ? 'fa-edit' : 'fa-plus-circle' }} me-2"></i>
                        {{ $editId ? 'Edit Client Type' : 'Add New Client Type' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" 
                               wire:model="name" placeholder="e.g., Construction, Law, Healthcare, Education">
                        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Slug</label>
                        <input type="text" class="form-control rounded-3 @error('slug') is-invalid @enderror" 
                               wire:model="slug" placeholder="construction">
                        @error('slug') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">Auto-generated from name (URL friendly)</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Icon (Font Awesome)</label>
                            <input type="text" class="form-control rounded-3" wire:model="icon" placeholder="fas fa-building">
                            <small class="text-muted">e.g., fas fa-building, fas fa-gavel, fas fa-heartbeat</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Color</label>
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" class="form-control form-control-color w-25" wire:model="color">
                                <input type="text" class="form-control rounded-3" wire:model="color" placeholder="#FE171B">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control rounded-3" wire:model="description" rows="2" 
                                  placeholder="Brief description of this client type"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                            <small class="text-muted">Lower numbers show first</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <i class="fas fa-check-circle text-success me-1"></i> Active
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-2">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" wire:click="closeModal">
                            <i class="fas fa-times me-2"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary px-4 py-2 rounded-3" wire:click="save">
                            <i class="fas fa-save me-2"></i> {{ $editId ? 'Update' : 'Save' }} Type
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
    .form-check-input:checked {
        background-color: #FE171B;
        border-color: #FE171B;
    }
</style>
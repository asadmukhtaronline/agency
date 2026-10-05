<?php

use Livewire\Component;
use App\Models\Service;
use Illuminate\Support\Str;

new class extends Component
{
    public $serviceId;
    public $title = '';
    public $slug = '';
    public $icon = '';
    public $short_description = '';
    public $description = '';
    public $is_active = true;
    public $order = 0;
    public $features = [];
    public $technologies = [];
    public $tags = '';
    
    public $featureInput = '';
    public $technologyInput = '';
    
    public function mount($id)
    {
        $service = Service::findOrFail($id);
        $this->serviceId = $service->id;
        $this->title = $service->title;
        $this->slug = $service->slug;
        $this->icon = $service->icon;
        $this->short_description = $service->short_description;
        $this->description = $service->description;
        $this->is_active = $service->is_active;
        $this->order = $service->order;
        $this->features = json_decode($service->features, true) ?? [];
        $this->technologies = json_decode($service->technologies, true) ?? [];
        $this->tags = $service->tags ?? '';
    }
    
    public function updatedTitle($value)
    {
        $this->slug = Str::slug($value);
    }
    
    public function addFeature()
    {
        if (!empty(trim($this->featureInput))) {
            $this->features[] = trim($this->featureInput);
            $this->featureInput = '';
        }
    }
    
    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }
    
    public function addTechnology()
    {
        if (!empty(trim($this->technologyInput))) {
            $this->technologies[] = trim($this->technologyInput);
            $this->technologyInput = '';
        }
    }
    
    public function removeTechnology($index)
    {
        unset($this->technologies[$index]);
        $this->technologies = array_values($this->technologies);
    }
    
    public function update()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'slug' => 'required|max:255|unique:services,slug,'.$this->serviceId,
            'icon' => 'required|max:100',
            'short_description' => 'required|min:20|max:500',
            'description' => 'required|min:50',
        ]);
        
        $service = Service::find($this->serviceId);
        $service->update([
            'title' => $this->title,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'order' => $this->order,
            'features' => json_encode($this->features),
            'technologies' => json_encode($this->technologies),
            'tags' => $this->tags,
        ]);
        
        session()->flash('message', 'Service updated successfully!');
        return redirect()->route('admin.services.index');
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
            <h2 class="fw-bold mb-0">Edit Service</h2>
            <p class="text-muted">Update your service information</p>
        </div>
        <a href="{{ route('admin.services.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Services
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Service Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="update">
                <!-- Title and Order -->
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Service Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('title') is-invalid @enderror" wire:model="title">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Display Order</label>
                        <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>
                
                <!-- Slug -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3 @error('slug') is-invalid @enderror" wire:model="slug">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Auto-generated from title</small>
                </div>
                
                <!-- Icon -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Icon Class <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3 @error('icon') is-invalid @enderror" wire:model="icon" placeholder="fa-solid fa-code">
                    @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($icon)
                        <div class="mt-2">
                            <span class="badge bg-light text-dark p-2 rounded-3">
                                Preview: <i class="{{ $icon }} ms-1"></i>
                            </span>
                        </div>
                    @endif
                    <small class="text-muted">Font Awesome icon class (e.g., fa-solid fa-code)</small>
                </div>
                
                <!-- Short Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Short Description <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3 @error('short_description') is-invalid @enderror" wire:model="short_description" rows="3"></textarea>
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">This will appear on the services listing page (20-500 characters)</small>
                </div>
                
                <!-- Full Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Description <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3 @error('description') is-invalid @enderror" wire:model="description" rows="10"></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <!-- Features -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Key Features</label>
                    <div class="input-group mb-2">
                        <input type="text" wire:model="featureInput" class="form-control rounded-3" placeholder="Enter a feature">
                        <button type="button" wire:click="addFeature" class="btn btn-primary rounded-3">Add Feature</button>
                    </div>
                    @if(count($features) > 0)
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach($features as $index => $feature)
                                <span class="badge bg-light text-dark p-2 rounded-3">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    {{ $feature }}
                                    <button type="button" wire:click="removeFeature({{ $index }})" class="btn-close ms-2 btn-sm"></button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <small class="text-muted">No features added yet. Click "Add Feature" to add.</small>
                    @endif
                </div>
                
                <!-- Technologies -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Technologies Used</label>
                    <div class="input-group mb-2">
                        <input type="text" wire:model="technologyInput" class="form-control rounded-3" placeholder="Enter a technology">
                        <button type="button" wire:click="addTechnology" class="btn btn-primary rounded-3">Add Technology</button>
                    </div>
                    @if(count($technologies) > 0)
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach($technologies as $index => $technology)
                                <span class="badge bg-info text-dark p-2 rounded-3">
                                    <i class="fas fa-code me-1"></i>
                                    {{ $technology }}
                                    <button type="button" wire:click="removeTechnology({{ $index }})" class="btn-close ms-2 btn-sm"></button>
                                </span>
                            @endforeach
                        </div>
                    @else
                        <small class="text-muted">No technologies added yet. Click "Add Technology" to add.</small>
                    @endif
                </div>
                
                <!-- Tags -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tags</label>
                    <input type="text" class="form-control rounded-3" wire:model="tags" placeholder="web development, programming, design">
                    <small class="text-muted">Comma-separated values</small>
                </div>
                
                <!-- Status -->
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                        <label class="form-check-label fw-semibold" for="isActive">
                            <i class="fas fa-toggle-{{ $is_active ? 'on text-success' : 'off text-secondary' }} me-1"></i>
                            Active (Visible on website)
                        </label>
                    </div>
                    <small class="text-muted">Inactive services will not be displayed on the frontend</small>
                </div>
                
                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Update Service
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .form-control:focus {
        border-color: #FE171B;
        box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
        border: none;
        transition: all 0.3s ease;
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
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .badge .btn-close {
        width: 0.5rem;
        height: 0.5rem;
        font-size: 0.5rem;
    }
</style>
<?php

use Livewire\Component;
use App\Models\Testimonial;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    public $client_name = '';
    public $client_position = '';
    public $client_company = '';
    public $client_avatar = null;
    public $content = '';
    public $rating = 5;
    public $service_used = '';
    public $is_featured = false;
    public $is_active = true;
    public $order = 0;
    
    public function save()
    {
        $this->validate([
            'client_name' => 'required|min:2|max:255',
            'client_position' => 'nullable|max:255',
            'client_company' => 'nullable|max:255',
            'client_avatar' => 'nullable|image|max:2048',
            'content' => 'required|min:20|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'service_used' => 'nullable|max:255',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer'
        ]);
        
        $avatarPath = null;
        if ($this->client_avatar) {
            $avatarPath = $this->client_avatar->store('testimonials', 'public');
        }
        
        Testimonial::create([
            'client_name' => $this->client_name,
            'client_position' => $this->client_position,
            'client_company' => $this->client_company,
            'client_avatar' => $avatarPath,
            'content' => $this->content,
            'rating' => $this->rating,
            'service_used' => $this->service_used,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ]);
        
        session()->flash('message', 'Testimonial created successfully!');
        return redirect()->route('admin.testimonials.index');
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
            <h2 class="fw-bold mb-0">Add Testimonial</h2>
            <p class="text-muted">Add client feedback and reviews</p>
        </div>
        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Testimonials
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i> Testimonial Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Client Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('client_name') is-invalid @enderror" wire:model="client_name">
                        @error('client_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Client Position</label>
                        <input type="text" class="form-control rounded-3" wire:model="client_position" placeholder="e.g., CEO, Founder">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Company Name</label>
                        <input type="text" class="form-control rounded-3" wire:model="client_company">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Service Used</label>
                        <input type="text" class="form-control rounded-3" wire:model="service_used" placeholder="e.g., Web Development, SEO">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Rating <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('rating') is-invalid @enderror" wire:model="rating">
                            <option value="5">★★★★★ (5)</option>
                            <option value="4">★★★★☆ (4)</option>
                            <option value="3">★★★☆☆ (3)</option>
                            <option value="2">★★☆☆☆ (2)</option>
                            <option value="1">★☆☆☆☆ (1)</option>
                        </select>
                        @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Display Order</label>
                        <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Client Avatar</label>
                    <input type="file" class="form-control rounded-3" wire:model="client_avatar" accept="image/*">
                    @error('client_avatar') <span class="text-danger small">{{ $message }}</span> @enderror
                    @if($client_avatar)
                        <div class="mt-2">
                            <img src="{{ $client_avatar->temporaryUrl() }}" width="80" height="80" class="rounded-circle shadow-sm">
                        </div>
                    @endif
                    <small class="text-muted">Upload client photo (Max: 2MB)</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Testimonial Content <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3 @error('content') is-invalid @enderror" wire:model="content" rows="5" placeholder="What did the client say about your service?"></textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isFeatured" wire:model="is_featured">
                            <label class="form-check-label fw-semibold" for="isFeatured">Feature this testimonial</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">Active (Visible on website)</label>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Save Testimonial
                    </button>
                    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
</style>
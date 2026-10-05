<?php

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    public $productId;
    public $name = '';
    public $slug = '';
    public $icon = '';
    public $category_id = '';
    public $short_description = '';
    public $description = '';
    public $is_active = true;
    public $order = 0;
    public $features = [];
    public $specifications = [];
    public $tags = '';
    
    // Image upload variables
    public $image = null;
    public $existingImage = '';
    public $gallery = [];
    public $existingGallery = [];
    public $removedGalleryImages = [];
    
    public $featureInput = '';
    public $specKey = '';
    public $specValue = '';
    
    public function mount($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->icon = $product->icon;
        $this->category_id = $product->category_id;
        $this->short_description = $product->short_description;
        $this->description = $product->description;
        $this->is_active = $product->is_active;
        $this->order = $product->order;
        $this->features = json_decode($product->features, true) ?? [];
        $this->specifications = json_decode($product->specifications, true) ?? [];
        $this->tags = $product->tags ?? '';
        $this->existingImage = $product->image;
        $this->existingGallery = json_decode($product->gallery, true) ?? [];
    }
    
    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }
    
    public function addFeature()
    {
        if (!empty(trim($this->featureInput))) {
            $this->features = array_merge($this->features, [trim($this->featureInput)]);
            $this->featureInput = '';
        }
    }
    
    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }
    
    public function addSpecification()
    {
        if (!empty(trim($this->specKey)) && !empty(trim($this->specValue))) {
            $this->specifications = array_merge($this->specifications, [[
                'key' => trim($this->specKey),
                'value' => trim($this->specValue)
            ]]);
            $this->specKey = '';
            $this->specValue = '';
        }
    }
    
    public function removeSpecification($index)
    {
        unset($this->specifications[$index]);
        $this->specifications = array_values($this->specifications);
    }
    
    public function removeExistingGalleryImage($index)
    {
        $this->removedGalleryImages[] = $this->existingGallery[$index];
        unset($this->existingGallery[$index]);
        $this->existingGallery = array_values($this->existingGallery);
    }
    
    public function removeGalleryImage($index)
    {
        unset($this->gallery[$index]);
        $this->gallery = array_values($this->gallery);
    }
    
    public function updatedImage()
    {
        $this->validate([
            'image' => 'image|max:2048',
        ]);
    }
    
    public function updatedGallery()
    {
        $this->validate([
            'gallery.*' => 'image|max:2048',
        ]);
    }
    
    public function update()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'slug' => 'required|max:255|unique:products,slug,' . $this->productId,
            'icon' => 'required|max:100',
            'category_id' => 'nullable|exists:product_categories,id',
            'short_description' => 'required|min:20|max:500',
            'description' => 'required|min:50',
            'image' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:2048',
        ]);
        
        $product = Product::find($this->productId);
        
        // Handle main image
        $imagePath = $this->existingImage;
        if ($this->image) {
            // Delete old image
            if ($this->existingImage && \Storage::disk('public')->exists($this->existingImage)) {
                \Storage::disk('public')->delete($this->existingImage);
            }
            $imagePath = $this->image->store('products', 'public');
        }
        
        // Handle gallery images
        $galleryPaths = $this->existingGallery;
        
        // Delete removed gallery images from storage
        foreach ($this->removedGalleryImages as $removedImage) {
            if (\Storage::disk('public')->exists($removedImage)) {
                \Storage::disk('public')->delete($removedImage);
            }
        }
        
        // Upload new gallery images
        if ($this->gallery) {
            foreach ($this->gallery as $galleryImage) {
                $galleryPaths[] = $galleryImage->store('products/gallery', 'public');
            }
        }
        
        $product->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'category_id' => $this->category_id ?: null,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image' => $imagePath,
            'gallery' => json_encode($galleryPaths),
            'features' => json_encode($this->features),
            'specifications' => json_encode($this->specifications),
            'tags' => $this->tags,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ]);
        
        session()->flash('message', 'Product updated successfully!');
        return redirect()->route('admin.products.index');
    }
    
    public function getCategoriesProperty()
    {
        return ProductCategory::where('is_active', true)->orderBy('name')->get();
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
            <h2 class="fw-bold mb-0">Edit Product</h2>
            <p class="text-muted">Update your product information</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Products
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Product Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="update">
                <!-- Basic Information -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Category</label>
                        <select class="form-select rounded-3 @error('category_id') is-invalid @enderror" wire:model="category_id">
                            <option value="">-- Select Category --</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
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
                    <small class="text-muted">Auto-generated from name</small>
                </div>
                
                <!-- Icon -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Icon Class <span class="text-danger">*</span></label>
                    <input type="text" class="form-control rounded-3 @error('icon') is-invalid @enderror" wire:model="icon" placeholder="fa-solid fa-box">
                    @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if($icon)
                        <div class="mt-2">
                            <span class="badge bg-light text-dark p-2 rounded-3">
                                Preview: <i class="{{ $icon }} ms-1"></i>
                            </span>
                        </div>
                    @endif
                    <small class="text-muted">Font Awesome icon class (e.g., fa-solid fa-box)</small>
                </div>
                
                <!-- Short Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Short Description <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3 @error('short_description') is-invalid @enderror" wire:model="short_description" rows="3"></textarea>
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">This will appear on the products listing page (20-500 characters)</small>
                </div>
                
                <!-- Full Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Full Description <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3 @error('description') is-invalid @enderror" wire:model="description" rows="10"></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <!-- Main Image -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Main Image</label>
                    @if($existingImage)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $existingImage) }}" width="100" class="rounded-3 shadow-sm">
                            <small class="d-block text-muted">Current image</small>
                        </div>
                    @endif
                    <input type="file" class="form-control rounded-3" wire:model="image" accept="image/*">
                    @error('image') <span class="text-danger small">{{ $message }}</span> @enderror
                    @if($image)
                        <div class="mt-2">
                            <img src="{{ $image->temporaryUrl() }}" width="100" class="rounded-3 shadow-sm">
                            <small class="text-muted">New image (will replace current)</small>
                        </div>
                    @endif
                    <small class="text-muted">Upload a new image to replace current one (Max: 2MB)</small>
                </div>
                
                <!-- Gallery Images -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Gallery Images</label>
                    
                    @if(count($existingGallery) > 0)
                        <div class="mb-2">
                            <label class="text-muted">Current Gallery Images:</label>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @foreach($existingGallery as $index => $img)
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/' . $img) }}" width="80" class="rounded-3 shadow-sm">
                                        <button type="button" wire:click="removeExistingGalleryImage({{ $index }})" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" style="width: 20px; height: 20px; padding: 0; font-size: 10px;">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <input type="file" class="form-control rounded-3" wire:model="gallery" multiple accept="image/*">
                    @error('gallery.*') <span class="text-danger small">{{ $message }}</span> @enderror
                    
                    @if($gallery)
                        <div class="mt-2">
                            <label class="text-muted">New Gallery Images:</label>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @foreach($gallery as $index => $img)
                                    <div class="position-relative">
                                        <img src="{{ $img->temporaryUrl() }}" width="80" class="rounded-3 shadow-sm">
                                        <button type="button" wire:click="removeGalleryImage({{ $index }})" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" style="width: 20px; height: 20px; padding: 0; font-size: 10px;">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <small class="text-muted">Upload additional images for gallery (Max each: 2MB)</small>
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
                
                <!-- Specifications -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Specifications</label>
                    <div class="row g-2 mb-2">
                        <div class="col-md-5">
                            <input type="text" wire:model="specKey" class="form-control rounded-3" placeholder="Specification (e.g., Weight)">
                        </div>
                        <div class="col-md-5">
                            <input type="text" wire:model="specValue" class="form-control rounded-3" placeholder="Value (e.g., 1.5kg)">
                        </div>
                        <div class="col-md-2">
                            <button type="button" wire:click="addSpecification" class="btn btn-primary w-100 rounded-3">Add</button>
                        </div>
                    </div>
                    @if(count($specifications) > 0)
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    @foreach($specifications as $index => $spec)
                                        <tr>
                                            <td style="width: 40%"><strong>{{ $spec['key'] }}</strong></td>
                                            <td style="width: 50%">{{ $spec['value'] }}</td>
                                            <td style="width: 10%">
                                                <button type="button" wire:click="removeSpecification({{ $index }})" class="btn btn-sm btn-danger">×</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <small class="text-muted">No specifications added yet.</small>
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
                    <small class="text-muted">Inactive products will not be displayed on the frontend</small>
                </div>
                
                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Update Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .badge .btn-close {
        width: 0.5rem;
        height: 0.5rem;
        font-size: 0.5rem;
    }
    .position-relative {
        position: relative;
    }
</style>
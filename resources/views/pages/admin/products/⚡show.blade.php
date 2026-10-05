<?php

use Livewire\Component;
use App\Models\Product;

new class extends Component
{
    public $product;
    
    public function mount($id)
    {
        $this->product = Product::with('category')->findOrFail($id);
    }
};

?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Product Details</h2>
            <p class="text-muted">View product information</p>
        </div>
        <div>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-warning rounded-3 me-2">
                <i class="fas fa-edit me-2"></i> Edit Product
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Products
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Product Images -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-image me-2"></i> Product Images</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Main Image -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Main Image</label>
                        <div class="text-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="img-fluid rounded-3 shadow-sm" style="max-height: 250px; width: auto;">
                            @else
                                <div class="bg-light rounded-3 p-5 text-center">
                                    <i class="fas fa-image fa-4x text-muted"></i>
                                    <p class="text-muted mt-2">No image available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Gallery Images -->
                    @if($product->gallery && count(json_decode($product->gallery, true)) > 0)
                        <div>
                            <label class="form-label fw-semibold">Gallery Images</label>
                            <div class="row g-2">
                                @foreach(json_decode($product->gallery, true) as $galleryImage)
                                    <div class="col-4">
                                        <img src="{{ asset('storage/' . $galleryImage) }}" class="img-fluid rounded-3 shadow-sm" style="height: 80px; width: 100%; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Product Information -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Product Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Product Name</label>
                            <h5 class="mt-1">{{ $product->name }}</h5>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Category</label>
                            <h5 class="mt-1">
                                @if($product->category)
                                    <span class="badge bg-primary">{{ $product->category->name }}</span>
                                @else
                                    <span class="badge bg-secondary">Uncategorized</span>
                                @endif
                            </h5>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Status</label>
                            <h5 class="mt-1">
                                @if($product->is_active)
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Inactive</span>
                                @endif
                            </h5>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Slug</label>
                            <p class="mt-1"><code>{{ $product->slug }}</code></p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Icon</label>
                            <p class="mt-1">
                                @if($product->icon)
                                    <i class="{{ $product->icon }} fa-2x text-primary"></i>
                                    <br><small class="text-muted">{{ $product->icon }}</small>
                                @else
                                    <span class="text-muted">No icon</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Display Order</label>
                            <p class="mt-1">{{ $product->order }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-semibold text-muted">Short Description</label>
                        <p class="mt-1">{{ $product->short_description }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-semibold text-muted">Full Description</label>
                        <div class="mt-1 p-3 bg-light rounded-3">
                            {!! $product->description !!}
                        </div>
                    </div>
                    
                    @if($product->tags)
                        <div class="mb-3">
                            <label class="fw-semibold text-muted">Tags</label>
                            <div class="mt-1">
                                @foreach(explode(',', $product->tags) as $tag)
                                    <span class="badge bg-secondary me-1">{{ trim($tag) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features & Specifications -->
    <div class="row">
        @if($product->features && count(json_decode($product->features, true)) > 0)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i> Key Features</h5>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled">
                            @foreach(json_decode($product->features, true) as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i> {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        
        @if($product->specifications && count(json_decode($product->specifications, true)) > 0)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i> Specifications</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach(json_decode($product->specifications, true) as $spec)
                                        <tr>
                                            <td style="width: 40%; background: #f8fafc;"><strong>{{ $spec['key'] }}</strong></td>
                                            <td style="width: 60%;">{{ $spec['value'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Additional Info -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i> Additional Information</h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6">
                    <label class="fw-semibold text-muted">Created At</label>
                    <p>{{ $product->created_at->format('F d, Y h:i A') }}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-muted">Last Updated</label>
                    <p>{{ $product->updated_at->format('F d, Y h:i A') }}</p>
                </div>
            </div>
            @if($product->deleted_at)
                <div class="row">
                    <div class="col-md-12">
                        <label class="fw-semibold text-danger">Deleted At</label>
                        <p class="text-danger">{{ $product->deleted_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .card {
        transition: transform 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
    }
    .table td {
        padding: 12px;
        vertical-align: middle;
    }
</style>
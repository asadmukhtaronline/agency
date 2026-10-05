<?php

use Livewire\Component;
use App\Models\PortfolioItem;

new class extends Component
{
    public $item;
    
    public function mount($id)
    {
        $this->item = PortfolioItem::findOrFail($id);
    }
};

?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Portfolio Details</h2>
            <p class="text-muted">View project information</p>
        </div>
        <div>
            <a href="{{ route('admin.portfolio.edit', $item->id) }}" class="btn btn-warning rounded-3 me-2">
                <i class="fas fa-edit me-2"></i> Edit Project
            </a>
            <a href="{{ route('admin.portfolio.index') }}" class="btn btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Portfolio
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Project Images -->
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-image me-2"></i> Project Images</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Featured Image -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Featured Image</label>
                        <div class="text-center">
                            @if($item->featured_image)
                                <img src="{{ asset('storage/' . $item->featured_image) }}" class="img-fluid rounded-3 shadow-sm" style="max-height: 250px; width: auto;">
                            @else
                                <div class="bg-light rounded-3 p-5 text-center">
                                    <i class="fas fa-image fa-4x text-muted"></i>
                                    <p class="text-muted mt-2">No image available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Gallery Images -->
                    @if($item->gallery && count(json_decode($item->gallery, true)) > 0)
                        <div>
                            <label class="form-label fw-semibold">Gallery Images</label>
                            <div class="row g-2">
                                @foreach(json_decode($item->gallery, true) as $galleryImage)
                                    <div class="col-4">
                                        <img src="{{ asset('storage/' . $galleryImage) }}" class="img-fluid rounded-3 shadow-sm" style="height: 80px; width: 100%; object-fit: cover; cursor: pointer;" onclick="window.open(this.src)">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Project Information -->
        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Project Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="fw-semibold text-muted">Project Title</label>
                            <h5 class="mt-1">{{ $item->title }}</h5>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="fw-semibold text-muted">Category</label>
                            <h5 class="mt-1">
                                <span class="badge bg-primary">{{ $item->category }}</span>
                            </h5>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Slug</label>
                            <p class="mt-1"><code>{{ $item->slug }}</code></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Status</label>
                            <p class="mt-1">
                                @if($item->is_active)
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Inactive</span>
                                @endif
                                @if($item->is_featured)
                                    <span class="badge bg-warning ms-2"><i class="fas fa-star me-1"></i> Featured</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Client Name</label>
                            <p class="mt-1">{{ $item->client_name ?? '—' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Completion Date</label>
                            <p class="mt-1">{{ $item->completion_date ? date('F d, Y', strtotime($item->completion_date)) : '—' }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-semibold text-muted">Project URL</label>
                        <p class="mt-1">
                            @if($item->project_url)
                                <a href="{{ $item->project_url }}" target="_blank" class="text-primary">{{ $item->project_url }} <i class="fas fa-external-link-alt fa-xs"></i></a>
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-semibold text-muted">Description</label>
                        <div class="mt-1 p-3 bg-light rounded-3">
                            {{ $item->description }}
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Display Order</label>
                            <p class="mt-1">{{ $item->order }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-semibold text-muted">Created At</label>
                            <p class="mt-1">{{ $item->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Technologies, Challenges, Solutions, Results -->
    <div class="row">
        @if($item->technologies && count(json_decode($item->technologies, true)) > 0)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-code me-2"></i> Technologies Used</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(json_decode($item->technologies, true) as $tech)
                                <span class="badge bg-info text-dark p-2 rounded-3">
                                    <i class="fas fa-code me-1"></i> {{ $tech }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        @if($item->challenges && count(json_decode($item->challenges, true)) > 0)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Challenges Faced</h5>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled">
                            @foreach(json_decode($item->challenges, true) as $challenge)
                                <li class="mb-2">
                                    <i class="fas fa-circle text-danger me-2" style="font-size: 8px;"></i> {{ $challenge }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    <div class="row">
        @if($item->solutions && count(json_decode($item->solutions, true)) > 0)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Solutions Provided</h5>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled">
                            @foreach(json_decode($item->solutions, true) as $solution)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i> {{ $solution }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        
        @if($item->results && count(json_decode($item->results, true)) > 0)
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Results Achieved</h5>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled">
                            @foreach(json_decode($item->results, true) as $result)
                                <li class="mb-2">
                                    <i class="fas fa-arrow-up text-primary me-2"></i> {{ $result }}
                                </li>
                            @endforeach
                        </ul>
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
                    <p>{{ $item->created_at->format('F d, Y h:i A') }}</p>
                </div>
                <div class="col-md-6">
                    <label class="fw-semibold text-muted">Last Updated</label>
                    <p>{{ $item->updated_at->format('F d, Y h:i A') }}</p>
                </div>
            </div>
            @if($item->deleted_at)
                <div class="row">
                    <div class="col-md-12">
                        <label class="fw-semibold text-danger">Deleted At</label>
                        <p class="text-danger">{{ $item->deleted_at->format('F d, Y h:i A') }}</p>
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
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .list-unstyled li {
        padding: 6px 0;
    }
</style>
<?php

use Livewire\Component;
use App\Models\Testimonial;

new class extends Component
{
    public $testimonial;
    
    public function mount($id)
    {
        $this->testimonial = Testimonial::findOrFail($id);
    }
};

?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Testimonial Details</h2>
            <p class="text-muted">View client feedback information</p>
        </div>
        <div>
            <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="btn btn-warning rounded-3 me-2">
                <i class="fas fa-edit me-2"></i> Edit Testimonial
            </a>
            <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Testimonials
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Client Information Card -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i> Client Information</h5>
                </div>
                <div class="card-body p-4 text-center">
                    <!-- Avatar -->
                    <div class="mb-3">
                        @if($testimonial->client_avatar)
                            <img src="{{ asset('storage/' . $testimonial->client_avatar) }}" width="120" height="120" class="rounded-circle shadow-sm" style="object-fit: cover;">
                        @else
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                <i class="fas fa-user fa-4x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    
                    <h4 class="fw-bold mb-1">{{ $testimonial->client_name }}</h4>
                    @if($testimonial->client_position)
                        <p class="text-muted mb-1">{{ $testimonial->client_position }}</p>
                    @endif
                    @if($testimonial->client_company)
                        <p class="text-muted">
                            <i class="fas fa-building me-1"></i> {{ $testimonial->client_company }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Testimonial Content Card -->
        <div class="col-md-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-star me-2"></i> Testimonial Details</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Rating -->
                    <div class="mb-3">
                        <label class="fw-semibold text-muted">Rating</label>
                        <div class="mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $testimonial->rating)
                                    <i class="fas fa-star text-warning fa-lg"></i>
                                @else
                                    <i class="far fa-star text-muted fa-lg"></i>
                                @endif
                            @endfor
                            <span class="ms-2 text-muted">({{ $testimonial->rating }}/5)</span>
                        </div>
                    </div>
                    
                    <!-- Service Used -->
                    @if($testimonial->service_used)
                        <div class="mb-3">
                            <label class="fw-semibold text-muted">Service Used</label>
                            <p class="mt-1">
                                <span class="badge bg-info">{{ $testimonial->service_used }}</span>
                            </p>
                        </div>
                    @endif
                    
                    <!-- Testimonial Content -->
                    <div class="mb-3">
                        <label class="fw-semibold text-muted">Testimonial</label>
                        <div class="mt-2 p-3 bg-light rounded-3">
                            <i class="fas fa-quote-left text-primary me-2 opacity-50"></i>
                            {{ $testimonial->content }}
                            <i class="fas fa-quote-right text-primary ms-2 opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Information -->
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Additional Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Display Order</label>
                            <p class="mt-1">{{ $testimonial->order }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Status</label>
                            <p class="mt-1">
                                @if($testimonial->is_active)
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Inactive</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Featured</label>
                            <p class="mt-1">
                                @if($testimonial->is_featured)
                                    <span class="badge bg-warning"><i class="fas fa-star me-1"></i> Featured</span>
                                @else
                                    <span class="badge bg-secondary">Not Featured</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="fw-semibold text-muted">Created At</label>
                            <p class="mt-1">{{ $testimonial->created_at->format('F d, Y h:i A') }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="fw-semibold text-muted">Last Updated</label>
                            <p>{{ $testimonial->updated_at->format('F d, Y h:i A') }}</p>
                        </div>
                        @if($testimonial->deleted_at)
                            <div class="col-md-6">
                                <label class="fw-semibold text-danger">Deleted At</label>
                                <p class="text-danger">{{ $testimonial->deleted_at->format('F d, Y h:i A') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
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
        padding: 6px 12px;
    }
    .bg-light {
        background-color: #f8fafc !important;
    }
    .fa-quote-left, .fa-quote-right {
        font-size: 0.9rem;
    }
</style>
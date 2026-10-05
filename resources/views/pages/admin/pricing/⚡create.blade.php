<?php

use Livewire\Component;
use App\Models\PricingPlan;
use App\Models\Country;
use App\Models\ServiceType;
use Illuminate\Support\Str;

new class extends Component
{
    public $country_id = '';
    public $service_type_id = '';
    public $name = '';
    public $slug = '';
    public $icon = '';
    public $price = '';
    public $period = 'month';
    public $features = [];
    public $is_popular = false;
    public $button_text = 'Get Started';
    public $button_link = '/quote';
    public $order = 0;
    public $is_active = true;
    
    public $featureInput = '';
    
    public function updatedName($value)
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
    
    public function save()
    {
        $this->validate([
            'country_id' => 'required|exists:countries,id',
            'service_type_id' => 'required|exists:service_types,id',
            'name' => 'required|min:3|max:255',
            'slug' => 'required|unique:pricing_plans,slug|max:255',
            'icon' => 'nullable|max:100',
            'price' => 'required|numeric|min:0',
            'period' => 'required|in:month,year,one-time',
            'features' => 'required|array|min:1',
            'is_popular' => 'boolean',
            'button_text' => 'required|max:255',
            'button_link' => 'required|max:255',
            'order' => 'integer',
            'is_active' => 'boolean'
        ]);
        
        PricingPlan::create([
            'country_id' => $this->country_id,
            'service_type_id' => $this->service_type_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'price' => $this->price,
            'period' => $this->period,
            'features' => json_encode($this->features),
            'is_popular' => $this->is_popular,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ]);
        
        session()->flash('message', 'Pricing plan created successfully!');
        return redirect()->route('admin.pricing.index');
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getServiceTypesProperty()
    {
        return ServiceType::where('is_active', true)->orderBy('order')->get();
    }
    
    public function getPeriodsProperty()
    {
        return [
            'month' => 'Monthly',
            'year' => 'Yearly',
            'one-time' => 'One Time'
        ];
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
            <h2 class="fw-bold mb-0">Create Pricing Plan</h2>
            <p class="text-muted">Add a new pricing plan for your services</p>
        </div>
        <a href="{{ route('admin.pricing.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Pricing
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-tag me-2"></i> Pricing Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <!-- Country and Service Type -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('country_id') is-invalid @enderror" wire:model="country_id">
                            <option value="">-- Select Country --</option>
                            @foreach($this->countries as $country)
                                <option value="{{ $country->id }}">
                                    {{ $country->name }} ({{ $country->currency_symbol }}{{ $country->currency_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('service_type_id') is-invalid @enderror" wire:model="service_type_id">
                            <option value="">-- Select Service Type --</option>
                            @foreach($this->serviceTypes as $type)
                                <option value="{{ $type->id }}">
                                    @if($type->icon)
                                        <i class="{{ $type->icon }} me-1"></i>
                                    @endif
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <!-- Plan Name and Slug -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g., Basic, Pro, Enterprise">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('slug') is-invalid @enderror" wire:model="slug">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Auto-generated from name</small>
                    </div>
                </div>
                
                <!-- Icon and Price -->
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Icon Class <span class="text-muted">(Optional)</span></label>
                        <input type="text" class="form-control rounded-3" wire:model="icon" placeholder="fa-solid fa-rocket">
                        @if($icon)
                            <div class="mt-2">
                                <span class="badge bg-light text-dark p-2 rounded-3">
                                    Preview: <i class="{{ $icon }} ms-1"></i>
                                </span>
                            </div>
                        @endif
                        <small class="text-muted">Font Awesome icon class</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text rounded-3">$</span>
                            <input type="number" step="0.01" class="form-control rounded-3 @error('price') is-invalid @enderror" wire:model="price" placeholder="0.00">
                        </div>
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Billing Period <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('period') is-invalid @enderror" wire:model="period">
                            <option value="month">Monthly</option>
                            <option value="year">Yearly</option>
                            <option value="one-time">One Time</option>
                        </select>
                        @error('period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-semibold">Display Order</label>
                        <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                </div>
                
                <!-- Features -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Features <span class="text-danger">*</span></label>
                    <div class="input-group mb-2">
                        <input type="text" wire:model="featureInput" class="form-control rounded-3" placeholder="Enter a feature (e.g., 5 Projects)">
                        <button type="button" wire:click="addFeature" class="btn btn-primary rounded-3">
                            <i class="fas fa-plus me-1"></i> Add Feature
                        </button>
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
                        <small class="text-muted text-danger">At least one feature is required</small>
                    @endif
                    @error('features') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                
                <!-- Button Settings -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Button Text</label>
                        <input type="text" class="form-control rounded-3" wire:model="button_text">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Button Link</label>
                        <input type="text" class="form-control rounded-3" wire:model="button_link" placeholder="/quote or /contact">
                    </div>
                </div>
                
                <!-- Popular and Status -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isPopular" wire:model="is_popular">
                            <label class="form-check-label fw-semibold" for="isPopular">
                                <i class="fas fa-star text-warning me-1"></i> Mark as Popular Plan
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">
                                <i class="fas fa-toggle-{{ $is_active ? 'on text-success' : 'off text-secondary' }} me-1"></i>
                                Active (Visible on website)
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Save Pricing Plan
                    </button>
                    <a href="{{ route('admin.pricing.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
    .btn-secondary {
        background: #64748b;
        border: none;
    }
    .btn-secondary:hover {
        background: #475569;
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
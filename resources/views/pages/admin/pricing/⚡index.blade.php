<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PricingPlan;
use App\Models\Country;
use App\Models\ServiceType;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $country_filter = '';
    public $service_type_filter = '';
    public $perPage = 10;
    
    public function deletePricing($id)
    {
        $pricing = PricingPlan::find($id);
        if ($pricing) {
            $pricing->delete();
            session()->flash('message', 'Pricing plan deleted successfully!');
        }
    }
    
    public function toggleStatus($id)
    {
        $pricing = PricingPlan::find($id);
        $pricing->is_active = !$pricing->is_active;
        $pricing->save();
        session()->flash('message', 'Pricing plan status updated!');
    }
    
    public function togglePopular($id)
    {
        $pricing = PricingPlan::find($id);
        $pricing->is_popular = !$pricing->is_popular;
        $pricing->save();
        session()->flash('message', 'Popular status updated!');
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingCountryFilter()
    {
        $this->resetPage();
    }
    
    public function updatingServiceTypeFilter()
    {
        $this->resetPage();
    }
    
    public function getPricingPlansProperty()
    {
        $query = PricingPlan::with(['country', 'serviceType']);
        
        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }
        
        if ($this->country_filter) {
            $query->where('country_id', $this->country_filter);
        }
        
        if ($this->service_type_filter) {
            $query->where('service_type_id', $this->service_type_filter);
        }
        
        return $query->orderBy('order')->paginate($this->perPage);
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getServiceTypesProperty()
    {
        return ServiceType::where('is_active', true)->orderBy('order')->get();
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
            <h2 class="fw-bold mb-0">Pricing Plans Management</h2>
            <p class="text-muted">Manage pricing plans for different countries and service types</p>
        </div>
        <a href="#" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Pricing Plan
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search plans...">
                    </div>
                </div>
                <div class="col-md-4 mb-2">
                    <select class="form-select rounded-3" wire:model.live="country_filter">
                        <option value="">All Countries</option>
                        @foreach($this->countries as $country)
                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <select class="form-select rounded-3" wire:model.live="service_type_filter">
                        <option value="">All Service Types</option>
                        @foreach($this->serviceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Pricing Plans Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-globe text-primary me-2"></i> </th>
                            <th class="py-3 px-4"><i class="fas fa-tags text-primary me-2"></i>  Type</th>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Plan </th>
                            <th class="py-3 px-4"><i class="fas fa-dollar-sign text-primary me-2"></i> Price</th>
                            <th class="py-3 px-4"><i class="fas fa-calendar text-primary me-2"></i> Period</th>
                            <th class="py-3 px-4"><i class="fas fa-star text-primary me-2"></i> Popular</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->pricingPlans as $plan)
                        <tr>
                            <td class="px-4">
                                <span class="fw-semibold">{{ $plan->country->name ?? 'N/A' }}</span>
                                <br>
                                <small class="text-muted">{{ $plan->country->currency_symbol ?? '' }}{{ $plan->country->currency_code ?? '' }}</small>
                            </td>
                            <td class="px-4">
                                @if($plan->serviceType)
                                    <span class="badge bg-secondary">
                                        @if($plan->serviceType->icon)
                                            <i class="{{ $plan->serviceType->icon }} me-1"></i>
                                        @endif
                                        {{ $plan->serviceType->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $plan->name }}</strong>
                                @if($plan->icon)
                                    <br><i class="{{ $plan->icon }} text-muted"></i>
                                @endif
                            </td>
                            <td class="px-4">
                                <span class="fw-bold text-primary">
                                    {{ $plan->country->currency_symbol ?? '$' }}{{ number_format($plan->price, 2) }}
                                </span>
                            </td>
                            <td class="px-4">
                                @if($plan->period == 'month')
                                    <span class="badge bg-info">Monthly</span>
                                @elseif($plan->period == 'year')
                                    <span class="badge bg-primary">Yearly</span>
                                @else
                                    <span class="badge bg-secondary">One Time</span>
                                @endif
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="togglePopular({{ $plan->id }})" {{ $plan->is_popular ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $plan->id }})" {{ $plan->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.pricing.edit', $plan->id) }}" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> 
                                    </a>
                                    <button wire:click="deletePricing({{ $plan->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-tag fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No pricing plans found</h5>
                                    <a href="#" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Pricing Plan
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <select wire:model.live="perPage" class="form-select w-auto d-inline-block rounded-3">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
                <div>
                    {{ $this->pricingPlans->links() }}
                </div>
            </div>
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
    .badge {
        font-weight: 500;
        padding: 6px 12px;
    }
</style>
<?php

use Livewire\Component;
use App\Models\Client;
use App\Models\Country;
use App\Models\ServiceType;
use App\Models\PricingPlan;
use App\Models\Estimate;
use App\Models\EstimateItem;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    // Basic Information
    public $country_id = '';
    public $client_id = '';
    public $service_type_id = '';
    public $pricing_plan_id = '';
    public $title = '';
    public $description = '';
    public $valid_until = '';
    public $notes = '';
    public $terms_conditions = '';
    public $tax_rate = 0;
    
    // Items Management
    public $items = [];
    public $item_name = '';
    public $item_description = '';
    public $item_quantity = 1;
    public $item_unit_price = 0;
    
    // Totals
    public $subtotal = 0;
    public $tax_amount = 0;
    public $total = 0;
    public $currency_symbol = '$';
    
    public function updatedCountryId()
    {
        $country = Country::find($this->country_id);
        if ($country) {
            $this->currency_symbol = $country->currency_symbol;
        }
        // Reset dependent fields
        $this->client_id = '';
        $this->service_type_id = '';
        $this->pricing_plan_id = '';
        $this->items = [];
        $this->title = '';
        $this->calculateTotals();
    }
    
    public function updatedServiceTypeId()
    {
        $this->pricing_plan_id = '';
        $this->items = [];
        $this->calculateTotals();
    }
    
    public function updatedPricingPlanId()
    {
        if ($this->pricing_plan_id) {
            $plan = PricingPlan::find($this->pricing_plan_id);
            if ($plan) {
                $this->title = $plan->name;
                $this->description = $plan->description ?? '';
                $this->tax_rate = 0;
                $this->items = [];
                
                $features = json_decode($plan->features, true) ?? [];
                foreach ($features as $feature) {
                    $this->items[] = [
                        'item_name' => $feature,
                        'description' => '',
                        'quantity' => 1,
                        'unit_price' => 0,
                        'total' => 0
                    ];
                }
                
                $this->items[] = [
                    'item_name' => 'Package Price',
                    'description' => $plan->name . ' Plan',
                    'quantity' => 1,
                    'unit_price' => $plan->price,
                    'total' => $plan->price
                ];
                
                $this->calculateTotals();
            }
        }
    }
    
    public function addItem()
    {
        if (!empty($this->item_name) && $this->item_unit_price > 0) {
            $total = $this->item_quantity * $this->item_unit_price;
            
            $this->items[] = [
                'item_name' => $this->item_name,
                'description' => $this->item_description,
                'quantity' => $this->item_quantity,
                'unit_price' => $this->item_unit_price,
                'total' => $total
            ];
            
            $this->item_name = '';
            $this->item_description = '';
            $this->item_quantity = 1;
            $this->item_unit_price = 0;
            $this->calculateTotals();
        }
    }
    
    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }
    
    public function calculateTotals()
    {
        $this->subtotal = array_sum(array_column($this->items, 'total'));
        $this->tax_amount = $this->subtotal * ($this->tax_rate / 100);
        $this->total = $this->subtotal + $this->tax_amount;
    }
    
    public function updatedTaxRate()
    {
        $this->calculateTotals();
    }
    
    public function save()
{
    $this->validate([
        'country_id' => 'required|exists:countries,id',
        'client_id' => 'required|exists:clients,id',
        'title' => 'required|min:3|max:255',
        'items' => 'required|array|min:1',
        'tax_rate' => 'nullable|numeric|min:0|max:100'
    ]);
    $validUntil = now()->addDays(30)->format('Y-m-d');
    // Fix: Properly handle valid_until - convert empty or invalid to null
    // $validUntil = null;
    // if (!empty($this->valid_until) && $this->valid_until != 'Worker' && $this->valid_until != 'worker') {
    //     $validUntil = $this->valid_until;
    // }
    
    $estimate = Estimate::create([
        'client_id' => $this->client_id,
        'country_id' => $this->country_id,
        'currency_symbol' => $this->currency_symbol,
        'service_type_id' => $this->service_type_id ?: null,
        'pricing_plan_id' => $this->pricing_plan_id ?: null,
        'estimate_number' => Estimate::generateNumber(),
        'title' => $this->title,
        'description' => $this->description,
        'subtotal' => $this->subtotal,
        'tax_rate' => $this->tax_rate,
        'tax_amount' => $this->tax_amount,
        'total' => $this->total,
        'valid_until' => $validUntil,
        'notes' => $this->notes,
        'terms_conditions' => $this->terms_conditions,
        'status' => 'draft',
        'created_by' => Auth::id()
    ]);
    
    foreach ($this->items as $item) {
        EstimateItem::create([
            'estimate_id' => $estimate->id,
            'item_name' => $item['item_name'],
            'description' => $item['description'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'total' => $item['total']
        ]);
    }
    
    // Generate and Save PDF
    $estimate->load(['client', 'items', 'country']);
    
    $pdf = Pdf::loadView('pdf.estimate', [
        'estimate' => $estimate,
        'items' => $estimate->items,
        'client' => $estimate->client,
        'country' => $estimate->country
    ]);
    
    $filename = 'estimate-' . $estimate->estimate_number . '.pdf';
    $filepath = 'estimates/' . $filename;
    Storage::disk('public')->put($filepath, $pdf->output());
    
    $estimate->update(['pdf_path' => $filepath]);
    
    session()->flash('message', 'Estimate created successfully with PDF!');
    return redirect()->route('admin.estimates.show', $estimate->id);
}
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
    }
    
    // Get clients filtered by selected country
    public function getClientsProperty()
    {
        $query = Client::where('is_active', true);
        
        if ($this->country_id) {
            $query->where('country_id', $this->country_id);
        }
        
        return $query->orderBy('name')->get();
    }
    
    public function getServiceTypesProperty()
    {
        return ServiceType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getPricingPlansProperty()
    {
        if ($this->service_type_id && $this->country_id) {
            return PricingPlan::where('service_type_id', $this->service_type_id)
                ->where('country_id', $this->country_id)
                ->where('is_active', true)
                ->orderBy('order')
                ->get();
        }
        return collect();
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
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Create Estimate</h2>
            <p class="text-muted">Create a new estimate for your client</p>
        </div>
        <a href="{{ route('admin.estimates.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Estimates
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Estimate Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <!-- Country, Client & Service Type -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Select Country <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" wire:model.live="country_id">
                            <option value="">-- Select Country --</option>
                            @foreach($this->countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->currency_symbol }})</option>
                            @endforeach
                        </select>
                        @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Select Client <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" wire:model="client_id">
                            <option value="">-- Select Client --</option>
                            @foreach($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Service Type</label>
                        <select class="form-select rounded-3" wire:model.live="service_type_id">
                            <option value="">-- Select Service Type (Optional) --</option>
                            @foreach($this->serviceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Package Selection (Shows only when service type and country selected) -->
                @if($service_type_id && $country_id)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Select Package</label>
                            <select class="form-select rounded-3" wire:model.live="pricing_plan_id">
                                <option value="">-- Select Package (Optional) --</option>
                                @foreach($this->pricingPlans as $plan)
                                    <option value="{{ $plan->id }}">
                                        {{ $plan->name }} - {{ $currency_symbol }}{{ number_format($plan->price, 2) }} / {{ $plan->period }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Estimate Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('title') is-invalid @enderror" wire:model="title">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-semibold">Estimate Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('title') is-invalid @enderror" wire:model="title">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif
                
                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control rounded-3" wire:model="description" rows="2" placeholder="Project description..."></textarea>
                </div>
                
                <!-- Items Section -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Estimate Items <span class="text-danger">*</span></label>
                    
                    <!-- Add Item Form -->
                    <div class="row g-2 mb-3 p-3 bg-light rounded-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control rounded-3" wire:model="item_name" placeholder="Item name *">
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control rounded-3" wire:model="item_description" placeholder="Description">
                        </div>
                        <div class="col-md-1">
                            <input type="number" class="form-control rounded-3" wire:model="item_quantity" placeholder="Qty">
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" class="form-control rounded-3" wire:model="item_unit_price" placeholder="Price">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100 rounded-3" wire:click="addItem">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        </div>
                    </div>
                    @error('items')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <!-- Items List -->
                    @if(count($items) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Quantity</th>
                                        <th>Unit Price</th>
                                        <th>Total</th>
                                        <th style="width: 50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                    <tr>
                                        <td><strong>{{ $item['item_name'] }}</strong></td>
                                        <td>{{ $item['description'] }}</td>
                                        <td>{{ $item['quantity'] }} </td>
                                        <td>{{ $currency_symbol }}{{ number_format($item['unit_price'], 2) }}</td>
                                        <td>{{ $currency_symbol }}{{ number_format($item['total'], 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" wire:click="removeItem({{ $index }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                        <td colspan="2">{{ $currency_symbol }}{{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> No items added yet. Add items above or select a package.
                        </div>
                    @endif
                </div>
                
                <!-- Tax & Totals -->
                <div class="row">
                    <div class="col-md-4 offset-md-8">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span>{{ $currency_symbol }}{{ number_format($subtotal, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax Rate (%):</span>
                                    <div class="w-50">
                                        <input type="number" step="0.01" class="form-control form-control-sm" wire:model="tax_rate">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax Amount:</span>
                                    <span>{{ $currency_symbol }}{{ number_format($tax_amount, 2) }}</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total:</span>
                                    <span class="text-primary">{{ $currency_symbol }}{{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estimate Disclaimer -->
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important Note:</strong> This is an estimate only, not an invoice. Prices are subject to change. This estimate is valid until the specified date.
                </div>
                
                <!-- Notes & Terms -->
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control rounded-3" wire:model="notes" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Terms & Conditions</label>
                        <textarea class="form-control rounded-3" wire:model="terms_conditions" rows="3" placeholder="Terms and conditions..."></textarea>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Save Estimate
                    </button>
                    <a href="{{ route('admin.estimates.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
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
    public $estimateId;
    
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
    
    public function mount($id)
    {
        $estimate = Estimate::with(['client', 'items', 'country', 'serviceType', 'pricingPlan'])->findOrFail($id);
        
        $this->estimateId = $estimate->id;
        $this->country_id = $estimate->country_id;
        $this->client_id = $estimate->client_id;
        $this->service_type_id = $estimate->service_type_id;
        $this->pricing_plan_id = $estimate->pricing_plan_id;
        $this->title = $estimate->title;
        $this->description = $estimate->description;
        $this->valid_until = $estimate->valid_until ? $estimate->valid_until->format('Y-m-d') : '';
        $this->notes = $estimate->notes;
        $this->terms_conditions = $estimate->terms_conditions;
        $this->tax_rate = $estimate->tax_rate;
        $this->currency_symbol = $estimate->currency_symbol ?? '$';
        
        // Load items
        foreach ($estimate->items as $item) {
            $this->items[] = [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total
            ];
        }
        
        $this->calculateTotals();
    }
    
    public function updatedCountryId()
    {
        $country = Country::find($this->country_id);
        if ($country) {
            $this->currency_symbol = $country->currency_symbol;
        }
        $this->calculateTotals();
    }
    
    public function updatedServiceTypeId()
    {
        $this->reset(['pricing_plan_id']);
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
                        'id' => null,
                        'item_name' => $feature,
                        'description' => '',
                        'quantity' => 1,
                        'unit_price' => 0,
                        'total' => 0
                    ];
                }
                
                $this->items[] = [
                    'id' => null,
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
                'id' => null,
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
        // If item has ID (existing in database), mark for deletion
        if (isset($this->items[$index]['id']) && $this->items[$index]['id']) {
            $itemToDelete = EstimateItem::find($this->items[$index]['id']);
            if ($itemToDelete) {
                $itemToDelete->delete();
            }
        }
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
    
    public function update()
    {
        $this->validate([
            'country_id' => 'required|exists:countries,id',
            'client_id' => 'required|exists:clients,id',
            'title' => 'required|min:3|max:255',
            'items' => 'required|array|min:1',
            'valid_until' => 'nullable|date',
            'tax_rate' => 'nullable|numeric|min:0|max:100'
        ]);
        
        // Update Estimate
        $estimate = Estimate::find($this->estimateId);
        $estimate->update([
            'client_id' => $this->client_id,
            'country_id' => $this->country_id,
            'currency_symbol' => $this->currency_symbol,
            'service_type_id' => $this->service_type_id ?: null,
            'pricing_plan_id' => $this->pricing_plan_id ?: null,
            'title' => $this->title,
            'description' => $this->description,
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'valid_until' => $this->valid_until ?: null,
            'notes' => $this->notes,
            'terms_conditions' => $this->terms_conditions,
        ]);
        
        // Update/Create Items
        foreach ($this->items as $item) {
            if (isset($item['id']) && $item['id']) {
                // Update existing item
                EstimateItem::where('id', $item['id'])->update([
                    'item_name' => $item['item_name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total']
                ]);
            } else {
                // Create new item
                EstimateItem::create([
                    'estimate_id' => $estimate->id,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total']
                ]);
            }
        }
        
        // Regenerate PDF after update
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
        
        session()->flash('message', 'Estimate updated successfully with PDF!');
        return redirect()->route('admin.estimates.show', $estimate->id);
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getClientsProperty()
    {
        return Client::where('is_active', true)->orderBy('name')->get();
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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Edit Estimate</h2>
            <p class="text-muted">Update estimate information</p>
        </div>
        <a href="{{ route('admin.estimates.show', $estimateId) }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-eye me-2"></i> View Estimate
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Estimate</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="update">
                <!-- Country, Client & Service Type -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" wire:model.live="country_id">
                            <option value="">-- Select Country --</option>
                            @foreach($this->countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->currency_symbol }})</option>
                            @endforeach
                        </select>
                        @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
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
                            <option value="">-- Select Service Type --</option>
                            @foreach($this->serviceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Package & Title -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Package</label>
                        <select class="form-select rounded-3" wire:model.live="pricing_plan_id">
                            <option value="">-- Select Package --</option>
                            @foreach($this->pricingPlans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} - {{ $currency_symbol }}{{ number_format($plan->price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('title') is-invalid @enderror" wire:model="title">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <!-- Description & Valid Until -->
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea class="form-control rounded-3" wire:model="description" rows="2"></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Valid Until</label>
                        <input type="date" class="form-control rounded-3" wire:model="valid_until">
                    </div>
                </div>
                
                <!-- Items Section -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Items <span class="text-danger">*</span></label>
                    
                    <!-- Add Item Form -->
                    <div class="row g-2 mb-3 p-3 bg-light rounded-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control rounded-3" wire:model="item_name" placeholder="Item name">
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
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                        </div>
                    </div>
                    
                    <!-- Items List -->
                    @if(count($items) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Description</th>
                                        <th>Qty</th>
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
                                        <td>{{ $item['quantity'] }}</td>
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
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                        <td colspan="2">{{ $currency_symbol }}{{ number_format($subtotal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">No items added.</div>
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
                                    <input type="number" step="0.01" class="form-control form-control-sm w-50" wire:model="tax_rate">
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
                
                <!-- Notes & Terms -->
                <div class="row mt-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control rounded-3" wire:model="notes" rows="3"></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Terms & Conditions</label>
                        <textarea class="form-control rounded-3" wire:model="terms_conditions" rows="3"></textarea>
                    </div>
                </div>
                
                <!-- Disclaimer -->
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> PDF will be automatically regenerated after update.
                </div>
                
                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Update Estimate
                    </button>
                    <a href="{{ route('admin.estimates.show', $estimateId) }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
</style>
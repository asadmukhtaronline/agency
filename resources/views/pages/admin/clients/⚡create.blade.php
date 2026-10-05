<?php

use Livewire\Component;
use App\Models\Client;
use App\Models\Country;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    public $country_id = '';
    public $name = '';
    public $email = '';
    public $phone = '';
    public $company = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $postal_code = '';
    public $gst_vat = '';
    public $notes = '';
    public $avatar = null;
    public $is_active = true;
    
    public function save()
    {
        $this->validate([
            'country_id' => 'required|exists:countries,id',
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'nullable|max:20',
            'company' => 'nullable|max:255',
            'address' => 'nullable|max:500',
            'city' => 'nullable|max:100',
            'state' => 'nullable|max:100',
            'postal_code' => 'nullable|max:20',
            'gst_vat' => 'nullable|max:50',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'boolean'
        ]);
        
        $avatarPath = null;
        if ($this->avatar) {
            $avatarPath = $this->avatar->store('clients', 'public');
        }
        
        Client::create([
            'country_id' => $this->country_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'gst_vat' => $this->gst_vat,
            'notes' => $this->notes,
            'avatar' => $avatarPath,
            'is_active' => $this->is_active,
        ]);
        
        session()->flash('message', 'Client created successfully!');
        return redirect()->route('admin.clients.index');
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
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
            <h2 class="fw-bold mb-0">Add New Client</h2>
            <p class="text-muted">Add a new client to manage quotations and projects</p>
        </div>
        <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Clients
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> Client Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <!-- Country Selection -->
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('country_id') is-invalid @enderror" wire:model="country_id">
                            <option value="">-- Select Country --</option>
                            @foreach($this->countries as $country)
                                <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->currency_symbol }})</option>
                            @endforeach
                        </select>
                        @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <!-- Basic Information -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Client Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3 @error('email') is-invalid @enderror" wire:model="email">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" class="form-control rounded-3" wire:model="phone">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Company</label>
                        <input type="text" class="form-control rounded-3" wire:model="company">
                    </div>
                </div>
                
                <!-- Address Information -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea class="form-control rounded-3" wire:model="address" rows="2"></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" class="form-control rounded-3" wire:model="city">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">State/Province</label>
                        <input type="text" class="form-control rounded-3" wire:model="state">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Postal Code</label>
                        <input type="text" class="form-control rounded-3" wire:model="postal_code">
                    </div>
                </div>
                
                <!-- Additional Information -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">GST/VAT Number</label>
                        <input type="text" class="form-control rounded-3" wire:model="gst_vat">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Client Avatar</label>
                        <input type="file" class="form-control rounded-3" wire:model="avatar" accept="image/*">
                        @if($avatar)
                            <div class="mt-2">
                                <img src="{{ $avatar->temporaryUrl() }}" width="50" height="50" class="rounded-circle">
                            </div>
                        @endif
                        <small class="text-muted">Upload client photo (Max: 2MB)</small>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea class="form-control rounded-3" wire:model="notes" rows="3" placeholder="Any additional notes about the client..."></textarea>
                </div>
                
                <!-- Status -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">Active Client</label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Save Client
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
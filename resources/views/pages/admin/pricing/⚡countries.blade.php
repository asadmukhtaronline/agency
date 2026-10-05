<?php

use Livewire\Component;
use App\Models\Country;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithFileUploads;
    
    public $showModal = false;
    public $editId = null;
    
    // Form fields
    public $name = '';
    public $code = '';
    public $currency_code = '';
    public $currency_symbol = '';
    public $flag = null;
    public $existingFlag = '';
    public $is_active = true;
    
    // New contact fields
    public $phone_number = '';
    public $whatsapp_number = '';
    public $email = '';
    
    public $search = '';
    
    public function openModal()
    {
        $this->reset(['name', 'code', 'currency_code', 'currency_symbol', 'flag', 'is_active', 'editId', 'existingFlag', 'phone_number', 'whatsapp_number', 'email']);
        $this->showModal = true;
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['name', 'code', 'currency_code', 'currency_symbol', 'flag', 'is_active', 'editId', 'existingFlag', 'phone_number', 'whatsapp_number', 'email']);
    }
    
    public function edit($id)
    {
        $country = Country::find($id);
        $this->editId = $country->id;
        $this->name = $country->name;
        $this->code = $country->code;
        $this->currency_code = $country->currency_code;
        $this->currency_symbol = $country->currency_symbol;
        $this->existingFlag = $country->flag;
        $this->is_active = $country->is_active;
        $this->phone_number = $country->phone_number;
        $this->whatsapp_number = $country->whatsapp_number;
        $this->email = $country->email;
        $this->showModal = true;
    }
    
    public function save()
    {
        $this->validate([
            'name' => 'required|min:2|max:255',
            'code' => 'required|size:2|unique:countries,code,' . $this->editId,
            'currency_code' => 'required|size:3',
            'currency_symbol' => 'required|max:10',
            'flag' => 'nullable|image|max:1024',
            'is_active' => 'boolean',
            'phone_number' => 'nullable|string|max:20',
            'whatsapp_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255'
        ]);
        
        // Handle flag upload
        $flagPath = $this->existingFlag;
        if ($this->flag) {
            // Delete old flag if exists
            if ($this->existingFlag && \Storage::disk('public')->exists($this->existingFlag)) {
                \Storage::disk('public')->delete($this->existingFlag);
            }
            $flagPath = $this->flag->store('flags', 'public');
        }
        
        if ($this->editId) {
            $country = Country::find($this->editId);
            $country->update([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'currency_code' => strtoupper($this->currency_code),
                'currency_symbol' => $this->currency_symbol,
                'flag' => $flagPath,
                'is_active' => $this->is_active,
                'phone_number' => $this->phone_number,
                'whatsapp_number' => $this->whatsapp_number,
                'email' => $this->email
            ]);
            session()->flash('message', 'Country updated successfully!');
        } else {
            Country::create([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'currency_code' => strtoupper($this->currency_code),
                'currency_symbol' => $this->currency_symbol,
                'flag' => $flagPath,
                'is_active' => $this->is_active,
                'phone_number' => $this->phone_number,
                'whatsapp_number' => $this->whatsapp_number,
                'email' => $this->email
            ]);
            session()->flash('message', 'Country created successfully!');
        }
        
        $this->closeModal();
    }
    
    public function deleteCountry($id)
    {
        $country = Country::find($id);
        if ($country && $country->pricingPlans()->count() == 0) {
            // Delete flag image if exists
            if ($country->flag && \Storage::disk('public')->exists($country->flag)) {
                \Storage::disk('public')->delete($country->flag);
            }
            $country->delete();
            session()->flash('message', 'Country deleted successfully!');
        } else {
            session()->flash('error', 'Cannot delete country with existing pricing plans!');
        }
    }
    
    public function toggleStatus($id)
    {
        $country = Country::find($id);
        $country->is_active = !$country->is_active;
        $country->save();
        session()->flash('message', 'Country status updated!');
    }
    
    #[Computed]
    public function countries()
    {
        return Country::where('name', 'like', '%'.$this->search.'%')
            ->orWhere('code', 'like', '%'.$this->search.'%')
            ->orderBy('name')
            ->get();
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
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Countries Management</h2>
            <p class="text-muted">Manage countries, currencies and contact information</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add New Country
        </button>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Search -->
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-3">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by country name or code...">
                </div>
            </div>
            
            <!-- Countries Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-flag text-primary me-2"></i> Flag</th>
                            <th class="py-3 px-4"><i class="fas fa-globe text-primary me-2"></i> Country</th>
                            <th class="py-3 px-4"><i class="fas fa-code text-primary me-2"></i> Code</th>
                            <th class="py-3 px-4"><i class="fas fa-money-bill-wave text-primary me-2"></i> Currency</th>
                            <th class="py-3 px-4"><i class="fas fa-phone text-primary me-2"></i> Contact</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->countries as $country)
                        <tr>
                            <td class="px-4">
                                @if($country->flag)
                                    <img src="{{ asset('storage/' . $country->flag) }}" width="40" height="30" class="rounded-2" style="object-fit: cover;">
                                @else
                                    <i class="fas fa-flag fa-2x text-muted"></i>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ $country->name }}</strong>
                            </td>
                            <td class="px-4">
                                <code>{{ $country->code }}</code>
                            </td>
                            <td class="px-4">
                                {{ $country->currency_code }} ({{ $country->currency_symbol }})
                            </td>
                            <td class="px-4">
                                @if($country->phone_number || $country->whatsapp_number || $country->email)
                                    <div class="d-flex flex-column gap-1">
                                        @if($country->phone_number)
                                            <small class="d-flex align-items-center gap-1">
                                                <i class="fas fa-phone fa-fw text-primary"></i>
                                                <a href="tel:{{ $country->phone_number }}" class="text-decoration-none">{{ $country->phone_number }}</a>
                                            </small>
                                        @endif
                                        @if($country->whatsapp_number)
                                            <small class="d-flex align-items-center gap-1">
                                                <i class="fab fa-whatsapp fa-fw text-success"></i>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $country->whatsapp_number) }}" target="_blank" class="text-decoration-none">{{ $country->whatsapp_number }}</a>
                                            </small>
                                        @endif
                                        @if($country->email)
                                            <small class="d-flex align-items-center gap-1">
                                                <i class="fas fa-envelope fa-fw text-danger"></i>
                                                <a href="mailto:{{ $country->email }}" class="text-decoration-none">{{ $country->email }}</a>
                                            </small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $country->id }})" {{ $country->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <button wire:click="edit({{ $country->id }})" class="btn btn-sm btn-info rounded-3 me-2" title="Edit Country">
                                        <i class="fas fa-edit me-1"></i> 
                                    </button>
                                    <button wire:click="deleteCountry({{ $country->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')" title="Delete Country">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-globe fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No countries found</h5>
                                    <button wire:click="openModal" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Your First Country
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal for Create/Edit Country -->
    @if($showModal)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas {{ $editId ? 'fa-edit' : 'fa-plus-circle' }} me-2"></i>
                        {{ $editId ? 'Edit Country' : 'Add New Country' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Basic Information -->
                    <h6 class="fw-bold mb-3 pb-2 border-bottom">
                        <i class="fas fa-info-circle text-primary me-2"></i> Basic Information
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Country Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name" placeholder="e.g., United States">
                        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Country Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('code') is-invalid @enderror" wire:model="code" placeholder="e.g., US" maxlength="2" style="text-transform: uppercase">
                            @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">2-letter country code (ISO)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Flag Image <span class="text-muted">(Optional)</span></label>
                            @if($existingFlag && !$flag)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $existingFlag) }}" width="60" height="40" class="rounded-2 shadow-sm">
                                    <small class="d-block text-muted">Current flag</small>
                                </div>
                            @endif
                            <input type="file" class="form-control rounded-3" wire:model="flag" accept="image/*">
                            @error('flag') <span class="text-danger small">{{ $message }}</span> @enderror
                            @if($flag)
                                <div class="mt-2">
                                    <img src="{{ $flag->temporaryUrl() }}" width="60" height="40" class="rounded-2 shadow-sm">
                                    <small class="text-muted">New flag (will replace current)</small>
                                </div>
                            @endif
                            <small class="text-muted">Upload country flag image (Max: 1MB)</small>
                        </div>
                    </div>
                    
                    <!-- Currency Information -->
                    <h6 class="fw-bold mb-3 mt-3 pb-2 border-bottom">
                        <i class="fas fa-money-bill-wave text-primary me-2"></i> Currency Information
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Currency Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('currency_code') is-invalid @enderror" wire:model="currency_code" placeholder="e.g., USD" maxlength="3" style="text-transform: uppercase">
                            @error('currency_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Currency Symbol <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('currency_symbol') is-invalid @enderror" wire:model="currency_symbol" placeholder="e.g., $">
                            @error('currency_symbol') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <h6 class="fw-bold mb-3 mt-3 pb-2 border-bottom">
                        <i class="fas fa-address-card text-primary me-2"></i> Contact Information
                    </h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Phone Number <span class="text-muted">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-3">
                                    <i class="fas fa-phone text-primary"></i>
                                </span>
                                <input type="text" class="form-control rounded-3 @error('phone_number') is-invalid @enderror" 
                                       wire:model="phone_number" placeholder="e.g., +1 234 567 8900">
                            </div>
                            @error('phone_number') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">Country's main contact number with country code</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">WhatsApp Number <span class="text-muted">(Optional)</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light rounded-3">
                                    <i class="fab fa-whatsapp text-success"></i>
                                </span>
                                <input type="text" class="form-control rounded-3 @error('whatsapp_number') is-invalid @enderror" 
                                       wire:model="whatsapp_number" placeholder="e.g., +1 234 567 8900">
                            </div>
                            @error('whatsapp_number') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">WhatsApp number with country code</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-muted">(Optional)</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light rounded-3">
                                <i class="fas fa-envelope text-danger"></i>
                            </span>
                            <input type="email" class="form-control rounded-3 @error('email') is-invalid @enderror" 
                                   wire:model="email" placeholder="e.g., contact@country.com">
                        </div>
                        @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">Official email address</small>
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-3 mt-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">
                                <i class="fas fa-toggle-on me-1"></i> Active (Visible on website)
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 pt-3">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-3" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary px-4 py-2 rounded-3" wire:click="save">
                            <i class="fas fa-save me-2"></i> {{ $editId ? 'Update' : 'Save' }} Country
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
    }
    .btn-info {
        background: #0dcaf0;
        border: none;
        transition: transform 0.2s;
    }
    .btn-info:hover {
        background: #0bb5d8;
        transform: translateY(-1px);
    }
    .btn-danger {
        transition: transform 0.2s;
    }
    .btn-danger:hover {
        transform: translateY(-1px);
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
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
    }
    table td {
        vertical-align: middle;
    }
    .btn-sm {
        padding: 0.4rem 0.8rem;
    }
    .gap-1 {
        gap: 0.25rem;
    }
</style>
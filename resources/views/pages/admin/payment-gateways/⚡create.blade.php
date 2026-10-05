<?php

use Livewire\Component;
use App\Models\PaymentGateway;

new class extends Component
{
    public $name = '';
    public $code = '';
    public $icon = '';
    public $settings = [];
    public $is_active = true;
    public $order = 0;
    
    public $settingKey = '';
    public $settingValue = '';
    
    public function updatedCode($value)
    {
        $this->code = strtolower(str_replace(' ', '_', $value));
    }
    
    public function addSetting()
    {
        if (!empty($this->settingKey) && !empty($this->settingValue)) {
            $this->settings[] = [
                'key' => $this->settingKey,
                'value' => $this->settingValue
            ];
            $this->settingKey = '';
            $this->settingValue = '';
        }
    }
    
    public function removeSetting($index)
    {
        unset($this->settings[$index]);
        $this->settings = array_values($this->settings);
    }
    
    public function save()
    {
        $this->validate([
            'name' => 'required|min:2|max:255',
            'code' => 'required|unique:payment_gateways,code|max:50',
            'icon' => 'nullable|max:100',
            'is_active' => 'boolean',
            'order' => 'integer'
        ]);
        
        PaymentGateway::create([
            'name' => $this->name,
            'code' => $this->code,
            'icon' => $this->icon,
            'settings' => json_encode($this->settings),
            'is_active' => $this->is_active,
            'order' => $this->order,
        ]);
        
        session()->flash('message', 'Payment gateway created successfully!');
        return redirect()->route('admin.payment-gateways.index');
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
            <h2 class="fw-bold mb-0">Add Payment Gateway</h2>
            <p class="text-muted">Add a new payment method for receiving payments</p>
        </div>
        <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Gateways
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Gateway Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Gateway Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Gateway Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('code') is-invalid @enderror" wire:model="code">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">e.g., paypal, stripe, bank_transfer</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Icon Class</label>
                        <input type="text" class="form-control rounded-3" wire:model="icon" placeholder="fab fa-paypal">
                        @if($icon)
                            <div class="mt-2">
                                <span class="badge bg-light text-dark p-2 rounded-3">
                                    Preview: <i class="{{ $icon }} ms-1"></i>
                                </span>
                            </div>
                        @endif
                        <small class="text-muted">Font Awesome icon class (optional)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Display Order</label>
                        <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                    </div>
                </div>
                
                <!-- Settings Section -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Gateway Settings</label>
                    <div class="row g-2 mb-2">
                        <div class="col-md-5">
                            <input type="text" wire:model="settingKey" class="form-control rounded-3" placeholder="Setting Key (e.g., api_key)">
                        </div>
                        <div class="col-md-5">
                            <input type="text" wire:model="settingValue" class="form-control rounded-3" placeholder="Setting Value">
                        </div>
                        <div class="col-md-2">
                            <button type="button" wire:click="addSetting" class="btn btn-primary w-100 rounded-3">Add</button>
                        </div>
                    </div>
                    @if(count($settings) > 0)
                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    @foreach($settings as $index => $setting)
                                        <tr>
                                            <td><strong>{{ $setting['key'] }}</strong></td>
                                            <td>{{ $setting['value'] }}</td>
                                            <td width="50">
                                                <button type="button" wire:click="removeSetting({{ $index }})" class="btn btn-sm btn-danger">×</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <small class="text-muted">No settings added yet. Add API keys or configuration settings.</small>
                    @endif
                </div>
                
                <!-- Status -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">Active (Available for payments)</label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Save Gateway
                    </button>
                    <a href="{{ route('admin.payment-gateways.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
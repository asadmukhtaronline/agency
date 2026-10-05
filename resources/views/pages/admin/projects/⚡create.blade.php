<?php

use Livewire\Component;
use App\Models\Client;
use App\Models\ServiceType;
use App\Models\Country;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $country_id = '';
    public $client_id = '';
    public $service_type_id = '';
    public $name = '';
    public $description = '';
    public $project_type = 'service';
    public $payment_type = 'one_time';
    public $total_amount = '';
    public $status = 'pending';
    public $priority = 'medium';
    public $start_date = '';
    public $end_date = '';
    public $notes = '';
    public $currency_symbol = '$';
    
    // Dynamic milestones
    public $milestones = [];
    
    public function mount()
    {
        // Initialize with one milestone for milestone-based
        $this->milestones = [
            ['name' => 'Milestone 1', 'amount' => 0, 'due_date' => '']
        ];
    }
    
    public function updatedCountryId()
    {
        $country = Country::find($this->country_id);
        if ($country) {
            $this->currency_symbol = $country->currency_symbol;
        }
        $this->client_id = '';
        $this->total_amount = '';
    }
    
    public function updatedPaymentType($value)
    {
        if ($value == 'one_time') {
            $this->milestones = [];
            $this->total_amount = '';
        } else {
            $this->milestones = [
                ['name' => 'Milestone 1', 'amount' => 0, 'due_date' => '']
            ];
            $this->total_amount = '';
        }
    }
    
    public function addMilestone()
    {
        $this->milestones[] = [
            'name' => 'Milestone ' . (count($this->milestones) + 1),
            'amount' => 0,
            'due_date' => ''
        ];
    }
    
    public function removeMilestone($index)
    {
        unset($this->milestones[$index]);
        $this->milestones = array_values($this->milestones);
        $this->calculateTotalFromMilestones();
    }
    
    public function calculateTotalFromMilestones()
    {
        if ($this->payment_type == 'milestone_based') {
            $this->total_amount = array_sum(array_column($this->milestones, 'amount'));
        }
    }
    
    public function updatedMilestones()
    {
        $this->calculateTotalFromMilestones();
    }
    
    public function save()
    {
        $this->validate([
            'country_id' => 'required|exists:countries,id',
            'client_id' => 'required|exists:clients,id',
            'service_type_id' => 'required|exists:service_types,id',
            'name' => 'required|min:3|max:255',
            'project_type' => 'required|in:service,product',
            'payment_type' => 'required|in:one_time,milestone_based',
            'total_amount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        // Validate milestones for milestone-based payment
        if ($this->payment_type == 'milestone_based') {
            if (count($this->milestones) == 0) {
                $this->addError('milestones', 'At least one milestone is required');
                return;
            }
            
            $totalFromMilestones = array_sum(array_column($this->milestones, 'amount'));
            
            // Check if any milestone amount is negative
            foreach ($this->milestones as $index => $milestone) {
                if ($milestone['amount'] < 0) {
                    $this->addError('milestones.' . $index . '.amount', 'Milestone amount cannot be negative');
                    return;
                }
            }
            
            // Check if total matches
            if (abs($totalFromMilestones - $this->total_amount) > 0.01) {
                $this->addError('total_amount', 'Total amount ($' . number_format($this->total_amount, 2) . ') must equal sum of milestone amounts ($' . number_format($totalFromMilestones, 2) . ')');
                return;
            }
            
            // Check if any milestone amount is zero
            foreach ($this->milestones as $index => $milestone) {
                if ($milestone['amount'] <= 0) {
                    $this->addError('milestones.' . $index . '.amount', 'Milestone ' . ($index + 1) . ' amount must be greater than 0');
                    return;
                }
            }
        }
        
        $project = Project::create([
            'client_id' => $this->client_id,
            'service_type_id' => $this->service_type_id,
            'project_code' => Project::generateCode(),
            'name' => $this->name,
            'description' => $this->description,
            'project_type' => $this->project_type,
            'payment_type' => $this->payment_type,
            'total_amount' => $this->total_amount,
            'paid_amount' => 0,
            'due_amount' => $this->total_amount,
            'payment_status' => 'unpaid',
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'notes' => $this->notes,
            'created_by' => Auth::id()
        ]);
        
        // Create milestones based on payment type
        if ($this->payment_type == 'one_time') {
            $project->milestones()->create([
                'milestone_name' => 'Project Completion',
                'description' => 'Full project delivery and completion',
                'amount' => $this->total_amount,
                'due_date' => $this->end_date,
                'status' => 'pending',
                'payment_status' => 'unpaid'
            ]);
        } else {
            foreach ($this->milestones as $milestone) {
                $project->milestones()->create([
                    'milestone_name' => $milestone['name'],
                    'description' => "Payment for " . $milestone['name'],
                    'amount' => $milestone['amount'],
                    'due_date' => $milestone['due_date'] ?: null,
                    'status' => 'pending',
                    'payment_status' => 'unpaid'
                ]);
            }
        }
        
        session()->flash('message', 'Project created successfully with ' . ($this->payment_type == 'one_time' ? '1' : count($this->milestones)) . ' milestone(s)!');
        return redirect()->route('admin.projects.show', $project->id);
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
    }
    
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
    
    public function getStatusesProperty()
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'on_hold' => 'On Hold',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];
    }
    
    public function getPrioritiesProperty()
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
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
            <h2 class="fw-bold mb-0">Create New Project</h2>
            <p class="text-muted">Add a new project to manage</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Projects
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i> Project Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
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
                        <select class="form-select rounded-3 @error('client_id') is-invalid @enderror" wire:model="client_id">
                            <option value="">-- Select Client --</option>
                            @foreach($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Service Type <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('service_type_id') is-invalid @enderror" wire:model="service_type_id">
                            <option value="">-- Select Service Type --</option>
                            @foreach($this->serviceTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('service_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <!-- Project Name & Type -->
                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label fw-semibold">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Project Type</label>
                        <select class="form-select rounded-3" wire:model="project_type">
                            <option value="service">Service Based</option>
                            <option value="product">Product Based</option>
                        </select>
                    </div>
                </div>
                
                <!-- Payment Type & Amount -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Payment Type <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" wire:model.live="payment_type">
                            <option value="one_time">One Time Payment</option>
                            <option value="milestone_based">Milestone Based Payment</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Total Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $currency_symbol }}</span>
                            <input type="number" step="0.01" class="form-control rounded-3 @error('total_amount') is-invalid @enderror" wire:model="total_amount" {{ $payment_type == 'milestone_based' ? 'readonly' : '' }}>
                        </div>
                        @error('total_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($payment_type == 'milestone_based')
                            <small class="text-muted">Total is auto-calculated from milestones below</small>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Priority</label>
                        <select class="form-select rounded-3" wire:model="priority">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                
                <!-- Dynamic Milestone Section -->
                @if($payment_type == 'milestone_based')
                    <div class="card bg-light mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong><i class="fas fa-flag-checkered me-2"></i> Milestone Payments</strong>
                            <button type="button" class="btn btn-sm btn-primary" wire:click="addMilestone">
                                <i class="fas fa-plus me-1"></i> Add Milestone
                            </button>
                        </div>
                        <div class="card-body">
                            @if(count($milestones) > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Milestone Name</th>
                                                <th>Amount ({{ $currency_symbol }})</th>
                                                <th>Due Date</th>
                                                <th style="width: 50px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($milestones as $index => $milestone)
                                            <tr>
                                                <td width="50">{{ $index + 1 }}</td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           placeholder="Milestone {{ $index + 1 }}" 
                                                           wire:model="milestones.{{ $index }}.name">
                                                </td>
                                                <td width="150">
                                                    <input type="number" step="0.01" class="form-control form-control-sm" 
                                                           placeholder="Amount" 
                                                           wire:model="milestones.{{ $index }}.amount">
                                                </td>
                                                <td width="150">
                                                    <input type="date" class="form-control form-control-sm" 
                                                           wire:model="milestones.{{ $index }}.due_date">
                                                </td>
                                                <td class="text-center">
                                                    @if(count($milestones) > 1)
                                                        <button type="button" class="btn btn-sm btn-danger" wire:click="removeMilestone({{ $index }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="2" class="text-end fw-bold">Total:</td>
                                                <td colspan="3">
                                                    <strong>{{ $currency_symbol }}{{ number_format(array_sum(array_column($milestones, 'amount')), 2) }}</strong>
                                                    @if(array_sum(array_column($milestones, 'amount')) > 0 && $total_amount > 0 && array_sum(array_column($milestones, 'amount')) != $total_amount)
                                                        <br>
                                                        <small class="text-danger">Must equal total amount: {{ $currency_symbol }}{{ number_format($total_amount, 2) }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-muted">No milestones added. Click "Add Milestone" to add.</p>
                                </div>
                            @endif
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i> 
                                Total amount will be auto-calculated from milestone amounts. Each milestone amount must be greater than 0.
                            </small>
                        </div>
                    </div>
                @endif
                
                <!-- Status & Dates -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Project Status</label>
                        <select class="form-select rounded-3" wire:model="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Start Date</label>
                        <input type="date" class="form-control rounded-3" wire:model="start_date">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">End Date</label>
                        <input type="date" class="form-control rounded-3" wire:model="end_date">
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control rounded-3" wire:model="description" rows="3"></textarea>
                </div>
                
                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea class="form-control rounded-3" wire:model="notes" rows="2"></textarea>
                </div>
                
                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Create Project
                    </button>
                    <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
    .table td, .table th {
        vertical-align: middle;
    }
    input[readonly] {
        background-color: #f8fafc;
    }
</style>
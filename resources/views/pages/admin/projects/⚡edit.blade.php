<?php

use Livewire\Component;
use App\Models\Client;
use App\Models\ServiceType;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $projectId;
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
    
    public function mount($id)
    {
        $project = Project::with(['client', 'serviceType'])->findOrFail($id);
        $this->projectId = $project->id;
        $this->client_id = $project->client_id;
        $this->service_type_id = $project->service_type_id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->project_type = $project->project_type;
        $this->payment_type = $project->payment_type;
        $this->total_amount = $project->total_amount;
        $this->status = $project->status;
        $this->priority = $project->priority;
        $this->start_date = $project->start_date ? $project->start_date->format('Y-m-d') : '';
        $this->end_date = $project->end_date ? $project->end_date->format('Y-m-d') : '';
        $this->notes = $project->notes;
    }
    
    public function update()
    {
        $this->validate([
            'client_id' => 'required|exists:clients,id',
            'service_type_id' => 'required|exists:service_types,id',
            'name' => 'required|min:3|max:255',
            'project_type' => 'required|in:service,product',
            'payment_type' => 'required|in:one_time,milestone_based',
            'total_amount' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        
        $project = Project::find($this->projectId);
        
        $project->update([
            'client_id' => $this->client_id,
            'service_type_id' => $this->service_type_id,
            'name' => $this->name,
            'description' => $this->description,
            'project_type' => $this->project_type,
            'payment_type' => $this->payment_type,
            'total_amount' => $this->total_amount,
            'due_amount' => $this->total_amount - $project->paid_amount,
            'status' => $this->status,
            'priority' => $this->priority,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'notes' => $this->notes,
        ]);
        
        // Update payment status based on new total amount
        $paidAmount = $project->paid_amount;
        $project->update([
            'payment_status' => $paidAmount >= $this->total_amount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid')
        ]);
        
        session()->flash('message', 'Project updated successfully!');
        return redirect()->route('admin.projects.index');
    }
    
    public function getClientsProperty()
    {
        return Client::where('is_active', true)->orderBy('name')->get();
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
            <h2 class="fw-bold mb-0">Edit Project</h2>
            <p class="text-muted">Update project information</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Projects
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Project</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="update">
                <!-- Client & Service Type -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Client <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('client_id') is-invalid @enderror" wire:model="client_id">
                            <option value="">-- Select Client --</option>
                            @foreach($this->clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                            @endforeach
                        </select>
                        @error('client_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
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
                        <label class="form-label fw-semibold">Payment Type</label>
                        <select class="form-select rounded-3" wire:model="payment_type">
                            <option value="one_time">One Time Payment</option>
                            <option value="milestone_based">Milestone Based Payment</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Total Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control rounded-3 @error('total_amount') is-invalid @enderror" wire:model="total_amount">
                        </div>
                        @error('total_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                
                <!-- Alert about milestones -->
                @if($payment_type == 'milestone_based')
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Milestones can be managed from the Milestones section.
                    </div>
                @endif
                
                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Update Project
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
</style>
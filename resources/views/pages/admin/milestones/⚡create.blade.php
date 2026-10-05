<?php

use Livewire\Component;
use App\Models\Project;
use App\Models\ProjectMilestone;

new class extends Component
{
    public $project_id = '';
    public $milestone_name = '';
    public $description = '';
    public $amount = '';
    public $due_date = '';
    public $status = 'pending';
    
    public function save()
    {
        $this->validate([
            'project_id' => 'required|exists:projects,id',
            'milestone_name' => 'required|min:3|max:255',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,in_progress,completed'
        ]);
        
        $project = Project::find($this->project_id);
        
        ProjectMilestone::create([
            'project_id' => $this->project_id,
            'milestone_name' => $this->milestone_name,
            'description' => $this->description,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'payment_status' => 'unpaid'
        ]);
        
        // Update project total amount
        $totalMilestoneAmount = $project->milestones()->sum('amount');
        $project->update([
            'total_amount' => $totalMilestoneAmount,
            'due_amount' => $totalMilestoneAmount - $project->paid_amount
        ]);
        
        session()->flash('message', 'Milestone created successfully!');
        return redirect()->route('admin.milestones.index');
    }
    
    public function getProjectsProperty()
    {
        return Project::orderBy('name')->get();
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
            <h2 class="fw-bold mb-0">Add Milestone</h2>
            <p class="text-muted">Add a new milestone to track project progress</p>
        </div>
        <a href="{{ route('admin.milestones.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Milestones
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-flag-checkered me-2"></i> Milestone Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Select Project <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('project_id') is-invalid @enderror" wire:model="project_id">
                            <option value="">-- Select Project --</option>
                            @foreach($this->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }} ({{ $project->project_code }})</option>
                            @endforeach
                        </select>
                        @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Milestone Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('milestone_name') is-invalid @enderror" wire:model="milestone_name">
                        @error('milestone_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control rounded-3 @error('amount') is-invalid @enderror" wire:model="amount">
                        </div>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Due Date</label>
                        <input type="date" class="form-control rounded-3" wire:model="due_date">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select rounded-3" wire:model="status">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control rounded-3" wire:model="description" rows="3"></textarea>
                </div>
                
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Create Milestone
                    </button>
                    <a href="{{ route('admin.milestones.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
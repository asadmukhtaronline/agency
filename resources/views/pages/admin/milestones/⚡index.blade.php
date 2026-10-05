<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ProjectMilestone;
use App\Models\Project;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status_filter = '';
    public $payment_filter = '';
    public $project_filter = '';
    public $perPage = 10;
    
    public function deleteMilestone($id)
    {
        $milestone = ProjectMilestone::find($id);
        if ($milestone) {
            // Check if payment already made
            if ($milestone->payments()->count() > 0) {
                session()->flash('error', 'Cannot delete milestone with existing payments!');
                return;
            }
            $milestone->delete();
            session()->flash('message', 'Milestone deleted successfully!');
        }
    }
    
    public function updateStatus($id, $status)
    {
        $milestone = ProjectMilestone::find($id);
        $milestone->update(['status' => $status]);
        
        // Update project progress based on completed milestones
        $project = $milestone->project;
        $completedMilestones = $project->milestones()->where('status', 'completed')->count();
        $totalMilestones = $project->milestones()->count();
        $progress = ($totalMilestones > 0) ? round(($completedMilestones / $totalMilestones) * 100) : 0;
        $project->update(['progress' => $progress]);
        
        session()->flash('message', 'Milestone status updated!');
    }
    
    public function getMilestonesProperty()
    {
        $query = ProjectMilestone::with(['project.client']);
        
        if ($this->search) {
            $query->where('milestone_name', 'like', '%'.$this->search.'%');
        }
        
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        
        if ($this->payment_filter) {
            $query->where('payment_status', $this->payment_filter);
        }
        
        if ($this->project_filter) {
            $query->where('project_id', $this->project_filter);
        }
        
        return $query->latest()->paginate($this->perPage);
    }
    
    public function getProjectsProperty()
    {
        return Project::orderBy('name')->get();
    }
    
    public function getStatusesProperty()
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed'
        ];
    }
    
    public function getPaymentStatusesProperty()
    {
        return [
            'unpaid' => 'Unpaid',
            'partial' => 'Partial',
            'paid' => 'Paid'
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
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Milestones Management</h2>
            <p class="text-muted">Manage project milestones and track progress</p>
        </div>
        <a href="{{ route('admin.milestones.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add Milestone
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3 mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search milestones...">
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="project_filter">
                        <option value="">All Projects</option>
                        @foreach($this->projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="payment_filter">
                        <option value="">All Payment Status</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select rounded-3" wire:model.live="perPage">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
            </div>
            
            <!-- Milestones Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4">ID</th>
                            <th class="py-3 px-4">Project</th>
                            <th class="py-3 px-4">Milestone</th>
                            <th class="py-3 px-4">Amount</th>
                            <th class="py-3 px-4">Due Date</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Payment</th>
                            <th class="py-3 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->milestones as $milestone)
                        <tr>
                            <td class="px-4">{{ $milestone->id }}</td>
                            <td class="px-4">
                                <strong>{{ $milestone->project->name }}</strong>
                                <br>
                                <small class="text-muted">Client: {{ $milestone->project->client->name }}</small>
                            </td>
                            <td class="px-4">
                                <strong>{{ $milestone->milestone_name }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($milestone->description, 40) }}</small>
                            </td>
                            <td class="px-4">
                                <strong>{{ $milestone->project->currency_symbol ?? '$' }}{{ number_format($milestone->amount, 2) }}</strong>
                            </td>
                            <td class="px-4">
                                @if($milestone->due_date)
                                    <span class="{{ $milestone->due_date < now() ? 'text-danger' : 'text-muted' }}">
                                        {{ $milestone->due_date->format('M d, Y') }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4">
                                <select class="form-select form-select-sm" wire:change="updateStatus({{ $milestone->id }}, $event.target.value)" style="width: 120px;">
                                    <option value="pending" {{ $milestone->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                    <option value="in_progress" {{ $milestone->status == 'in_progress' ? 'selected' : '' }}>🔄 In Progress</option>
                                    <option value="completed" {{ $milestone->status == 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                </select>
                            </td>
                            <td class="px-4">
                                @php
                                    $paymentColors = [
                                        'unpaid' => 'danger',
                                        'partial' => 'warning',
                                        'paid' => 'success'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $paymentColors[$milestone->payment_status] }}">
                                    {{ ucfirst($milestone->payment_status) }}
                                </span>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.milestones.edit', $milestone->id) }}" class="btn btn-sm btn-info rounded-3 me-2">
                                        <i class="fas fa-edit me-1"></i> 
                                    </a>
                                    <button wire:click="deleteMilestone({{ $milestone->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-flag-checkered fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No milestones found</h5>
                                    <a href="{{ route('admin.milestones.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add Milestone
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->milestones->links() }}
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
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .input-group-text {
        border-right: none;
    }
    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }
</style>
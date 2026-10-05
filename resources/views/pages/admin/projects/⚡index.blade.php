<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Project;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $status_filter = '';
    public $payment_filter = '';
    public $priority_filter = '';
    public $perPage = 10;
    
    public function deleteProject($id)
    {
        $project = Project::find($id);
        if ($project) {
            // Check if project has payments
            if ($project->payments()->count() > 0) {
                session()->flash('error', 'Cannot delete project with existing payments!');
                return;
            }
            // Delete milestones first
            $project->milestones()->delete();
            $project->delete();
            session()->flash('message', 'Project deleted successfully!');
        }
    }
    
    public function getProjectsProperty()
    {
        $query = Project::with(['client', 'serviceType']);
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('project_code', 'like', '%'.$this->search.'%')
                  ->orWhereHas('client', function($cq) {
                      $cq->where('name', 'like', '%'.$this->search.'%');
                  });
            });
        }
        
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }
        
        if ($this->payment_filter) {
            $query->where('payment_status', $this->payment_filter);
        }
        
        if ($this->priority_filter) {
            $query->where('priority', $this->priority_filter);
        }
        
        return $query->latest()->paginate($this->perPage);
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
    
    public function getPaymentStatusesProperty()
    {
        return [
            'unpaid' => 'Unpaid',
            'partial' => 'Partial',
            'paid' => 'Paid'
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
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Projects Management</h2>
            <p class="text-muted">Manage all your projects</p>
        </div>
        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> New Project
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
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search projects...">
                    </div>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="on_hold">On Hold</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="payment_filter">
                        <option value="">All Payment</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="priority_filter">
                        <option value="">All Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
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
            
            <!-- Projects Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4">
                                <i class="fas fa-project-diagram text-primary me-2"></i> Project
                            </th>
                            <th class="py-3 px-4">
                                <i class="fas fa-user text-primary me-2"></i> Client
                            </th>
                            <th class="py-3 px-4">
                                <i class="fas fa-dollar-sign text-primary me-2"></i> Amount
                            </th>
                            <th class="py-3 px-4">
                                <i class="fas fa-hand-holding-usd text-primary me-2"></i> Paid
                            </th>
                            <th class="py-3 px-4">
                                <i class="fas fa-flag text-primary me-2"></i> Priority
                            </th>
                            <th class="py-3 px-4 text-center">
                                <i class="fas fa-cog text-primary me-2"></i> Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->projects as $project)
                        <tr>
                            <td class="px-4">
                                <strong>{{ Str::limit($project->name, 30) }}</strong>
                                <br>
                                <small class="text-muted">{{ $project->project_type == 'service' ? 'Service' : 'Product' }}</small>
                            </td>
                            <td class="px-4">
                                {{ $project->client->name }}
                                <br>
                                <small class="text-muted">{{ $project->client->email }}</small>
                            </td>
                            <td class="px-4">
                                <strong>${{ number_format($project->total_amount, 2) }}</strong>
                            </td>
                            <td class="px-4">
                                ${{ number_format($project->paid_amount, 2) }}
                                <br>
                                <small class="text-muted">Due: ${{ number_format($project->due_amount, 2) }}</small>
                            </td>
                            <td class="px-4">
                                @php
                                    $priorityColors = [
                                        'low' => 'secondary',
                                        'medium' => 'info',
                                        'high' => 'warning',
                                        'urgent' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $priorityColors[$project->priority] }}">
                                    {{ ucfirst($project->priority) }}
                                </span>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-sm btn-success rounded-3 me-2" title="View">
                                        <i class="fas fa-eye me-1"></i>
                                    </a>
                                    <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-sm btn-info rounded-3 me-2" title="Edit">
                                        <i class="fas fa-edit me-1"></i>
                                    </a>
                                    <button wire:click="deleteProject({{ $project->id }})" class="btn btn-sm btn-danger rounded-3" title="Delete" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt me-1"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-project-diagram fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No projects found</h5>
                                    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Create Your First Project
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->projects->links() }}
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
    .progress {
        background-color: #e2e8f0;
        border-radius: 10px;
    }
    .badge {
        font-weight: 500;
        padding: 5px 10px;
    }
</style>
<?php

use Livewire\Component;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public $project;
    public $activeTab = 'overview';
    
    public function mount($id)
    {
        $this->project = Project::with(['client', 'serviceType', 'milestones', 'payments'])->findOrFail($id);
    }
    
    public function downloadProjectPlan()
    {
        $project = $this->project;
        $milestones = $project->milestones;
        $client = $project->client;
        
        $pdf = Pdf::loadView('pdf.project-plan', [
            'project' => $project,
            'milestones' => $milestones,
            'client' => $client
        ]);
        
        $filename = 'project-plan-' . $project->project_code . '.pdf';
        
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
    
    public function getMilestonesProperty()
    {
        return $this->project->milestones;
    }
    
    public function getPaymentsProperty()
    {
        return $this->project->payments;
    }
    
    public function getTotalMilestonesProperty()
    {
        return $this->project->milestones->count();
    }
    
    public function getCompletedMilestonesProperty()
    {
        return $this->project->milestones->where('status', 'completed')->count();
    }
    
    public function getProgressProperty()
    {
        $total = $this->totalMilestones;
        if ($total == 0) return 0;
        return round(($this->completedMilestones / $total) * 100);
    }
    
    public function getTotalPaidProperty()
    {
        return $this->project->payments->sum('amount');
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
            <h2 class="fw-bold mb-0">{{ $project->name }}</h2>
            <p class="text-muted">Project Code: <code>{{ $project->project_code }}</code></p>
        </div>
        <div>
            <button wire:click="downloadProjectPlan" class="btn btn-danger btn-sm rounded-3 me-2">
                <i class="fas fa-file-pdf me-2"></i>  Project Plan
            </button>
            <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-warning btn-sm rounded-3 me-2">
                <i class="fas fa-edit me-2"></i> Edit Project
            </a>
            <a href="{{ route('admin.payments.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-success rounded-3 me-2">
                <i class="fas fa-plus-circle me-2"></i> Record Payment
            </a>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>
    
    <!-- Project Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Total Amount</p>
                            <h3 class="mb-0 fw-bold">${{ number_format($project->total_amount, 2) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-dollar-sign fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Paid Amount</p>
                            <h3 class="mb-0 fw-bold text-success">${{ number_format($project->paid_amount, 2) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Due Amount</p>
                            <h3 class="mb-0 fw-bold text-danger">${{ number_format($project->due_amount, 2) }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-clock fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Progress</p>
                            <h3 class="mb-0 fw-bold">{{ $this->progress }}%</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $this->progress }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button wire:click="$set('activeTab', 'overview')" class="nav-link {{ $activeTab == 'overview' ? 'active' : '' }}">
                        <i class="fas fa-info-circle me-2"></i>Overview
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('activeTab', 'milestones')" class="nav-link {{ $activeTab == 'milestones' ? 'active' : '' }}">
                        <i class="fas fa-flag-checkered me-2"></i>Milestones ({{ $this->totalMilestones }})
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="$set('activeTab', 'payments')" class="nav-link {{ $activeTab == 'payments' ? 'active' : '' }}">
                        <i class="fas fa-money-bill-wave me-2"></i>Payments ({{ $this->payments->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <!-- Overview Tab -->
            @if($activeTab == 'overview')
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-3">Project Information</h5>
                        <table class="table table-borderless">
                            <tr><td width="150"><strong>Client:</strong></td><td>{{ $project->client->name }}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>{{ $project->client->email }}</td></tr>
                            @if($project->client->phone)<tr><td><strong>Phone:</strong></td><td>{{ $project->client->phone }}</td></tr>@endif
                            @if($project->client->company)<tr><td><strong>Company:</strong></td><td>{{ $project->client->company }}</td></tr>@endif
                            <tr><td><strong>Service Type:</strong></td><td>{{ $project->serviceType->name ?? 'N/A' }}</td></tr>
                            <tr><td><strong>Project Type:</strong></td><td>{{ ucfirst($project->project_type) }}</td></tr>
                            <tr><td><strong>Payment Type:</strong></td><td>{{ ucfirst(str_replace('_', ' ', $project->payment_type)) }}</td></tr>
                            <tr><td><strong>Priority:</strong></td>
                                <td>@php $priorityColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger']; @endphp
                                <span class="badge bg-{{ $priorityColors[$project->priority] }}">{{ ucfirst($project->priority) }}</span></td>
                            </tr>
                            <tr><td><strong>Status:</strong></td>
                                <td>@php $statusColors = ['pending'=>'warning','in_progress'=>'info','on_hold'=>'secondary','completed'=>'success','cancelled'=>'danger']; @endphp
                                <span class="badge bg-{{ $statusColors[$project->status] }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span></td>
                            </tr>
                            <tr><td><strong>Start Date:</strong></td><td>{{ $project->start_date ? $project->start_date->format('M d, Y') : 'N/A' }}</td></tr>
                            <tr><td><strong>End Date:</strong></td><td>{{ $project->end_date ? $project->end_date->format('M d, Y') : 'N/A' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="fw-bold mb-3">Description</h5>
                        <p>{{ $project->description ?? 'No description provided.' }}</p>
                        @if($project->notes)
                            <h5 class="fw-bold mb-3 mt-4">Notes</h5>
                            <p>{{ $project->notes }}</p>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Milestones Tab -->
            @if($activeTab == 'milestones')
                @if($this->milestones->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr><th>#</th><th>Milestone Name</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Payment Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach($this->milestones as $index => $milestone)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $milestone->milestone_name }}</strong></td>
                                    <td>${{ number_format($milestone->amount, 2) }}</td>
                                    <td>{{ $milestone->due_date ? $milestone->due_date->format('M d, Y') : 'N/A' }}</td>
                                    <td>@php $sColors = ['pending'=>'warning','in_progress'=>'info','completed'=>'success']; @endphp
                                        <span class="badge bg-{{ $sColors[$milestone->status] }}">{{ ucfirst($milestone->status) }}</span></td>
                                    <td>@php $pColors = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success']; @endphp
                                        <span class="badge bg-{{ $pColors[$milestone->payment_status] }}">{{ ucfirst($milestone->payment_status) }}</span></td>
                                    <td><a href="{{ route('admin.milestones.edit', $milestone->id) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light"><tr><td colspan="2" class="text-end fw-bold">Total:</td><td colspan="5"><strong>${{ number_format($this->milestones->sum('amount'), 2) }}</strong></td></tr></tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5"><i class="fas fa-flag-checkered fa-3x text-muted mb-3 d-block"></i><p>No milestones added yet.</p><a href="{{ route('admin.milestones.create', ['project_id' => $project->id]) }}" class="btn btn-primary">Add Milestone</a></div>
                @endif
            @endif
            
            <!-- Payments Tab -->
            @if($activeTab == 'payments')
                @if($this->payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light"><tr><th>Payment #</th><th>Date</th><th>Milestone</th><th>Amount</th><th>Method</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                                @foreach($this->payments as $payment)
                                <tr>
                                    <td><code>{{ $payment->payment_number }}</code></td>
                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td>{{ $payment->milestone->milestone_name ?? 'Direct Payment' }}</td>
                                    <td><strong class="text-success">${{ number_format($payment->amount, 2) }}</strong></td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                    <td>@php $stColors = ['pending'=>'warning','completed'=>'success','failed'=>'danger']; @endphp
                                        <span class="badge bg-{{ $stColors[$payment->status] }}">{{ ucfirst($payment->status) }}</span></td>
                                    <td><a href="{{ route('admin.payments.show', $payment->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light"><tr><td colspan="3" class="text-end fw-bold">Total Paid:</td><td colspan="3"><strong class="text-success">${{ number_format($this->totalPaid, 2) }}</strong></td></tr></tfoot>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5"><i class="fas fa-money-bill-wave fa-3x text-muted mb-3 d-block"></i><p>No payments recorded yet.</p><a href="{{ route('admin.payments.create', ['project_id' => $project->id]) }}" class="btn btn-primary">Record Payment</a></div>
                @endif
            @endif
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary { background: linear-gradient(135deg, #FE171B, #cc1215); }
    .rounded-4 { border-radius: 20px; }
    .rounded-3 { border-radius: 12px; }
    .nav-tabs .nav-link { border: none; color: #64748b; font-weight: 500; padding: 10px 20px; }
    .nav-tabs .nav-link.active { color: #FE171B; border-bottom: 2px solid #FE171B; background: transparent; }
    .nav-tabs .nav-link:hover { color: #FE171B; }
    .table-borderless td, .table-borderless th { padding: 8px 0; }
    .progress { background-color: #e2e8f0; border-radius: 10px; }
</style>
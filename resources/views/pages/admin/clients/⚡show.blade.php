<?php

use Livewire\Component;
use App\Models\Client;
use App\Models\Project;
use App\Models\Estimate;

new class extends Component
{
    public $clientId;
    public $client;
    public $projects;
    public $estimates;
    public $totalProjects = 0;
    public $totalEstimates = 0;
    public $totalProjectAmount = 0;
    public $totalEstimateAmount = 0;
    
    public function mount($id)
    {
        $this->clientId = $id;
        $this->loadClient();
        $this->loadProjects();
        $this->loadEstimates();
        $this->calculateStats();
    }
    
    public function loadClient()
    {
        $this->client = Client::with('country')->find($this->clientId);
        
        if (!$this->client) {
            session()->flash('error', 'Client not found!');
            return redirect()->route('admin.clients.index');
        }
    }
    
    public function loadProjects()
    {
        $this->projects = Project::where('client_id', $this->clientId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $this->totalProjects = $this->projects->count();
        $this->totalProjectAmount = $this->projects->sum('total_amount');
    }
    
    public function loadEstimates()
    {
        $this->estimates = Estimate::where('client_id', $this->clientId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $this->totalEstimates = $this->estimates->count();
        $this->totalEstimateAmount = $this->estimates->sum('total');
    }
    
    public function calculateStats()
    {
        // Additional stats if needed
    }
};

?>

<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Client Details</h2>
            <p class="text-muted">Complete client information and activity</p>
        </div>
        <div>
            <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Clients
            </a>
            <a href="{{ route('admin.clients.edit', $clientId) }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-2"></i> Edit Client
            </a>
            <a href="{{ route('admin.estimates.create') }}?client_id={{ $clientId }}" class="btn btn-info">
                <i class="fas fa-file-invoice me-2"></i> Create Estimate
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Client Information Card -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    @if($client->avatar)
                        <img src="{{ asset('storage/' . $client->avatar) }}" class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover;">
                    @else
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px;">
                            <span class="text-white display-1 fw-bold">{{ strtoupper(substr($client->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    
                    <h4 class="mb-1">{{ $client->name }}</h4>
                    <p class="text-muted">{{ $client->company ?? 'No company' }}</p>
                    
                    <div class="mt-2">
                        @if($client->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Contact Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th width="35%">Email:</th>
                            <td>{{ $client->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $client->phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Company:</th>
                            <td>{{ $client->company ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>GST/VAT:</th>
                            <td>{{ $client->gst_vat ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Address Information -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Address</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1">{{ $client->address ?? 'No address' }}</p>
                    <p class="mb-0">
                        {{ $client->city ? $client->city . ', ' : '' }}
                        {{ $client->state ? $client->state . ', ' : '' }}
                        {{ $client->postal_code ?? '' }}
                    </p>
                    <p class="mb-0 mt-2">
                        <strong>Country:</strong> {{ $client->country->name ?? 'Not selected' }}
                    </p>
                </div>
            </div>
            
            <!-- Notes -->
            @if($client->notes)
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $client->notes }}</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Right Column - Projects and Estimates -->
        <div class="col-md-8">
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 opacity-75">Total Projects</h6>
                                    <h2 class="mb-0 fw-bold">{{ $totalProjects }}</h2>
                                </div>
                                <i class="fas fa-project-diagram fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 opacity-75">Total Estimates</h6>
                                    <h2 class="mb-0 fw-bold">{{ $totalEstimates }}</h2>
                                </div>
                                <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 opacity-75">Project Value</h6>
                                    <h5 class="mb-0 fw-bold">${{ number_format($totalProjectAmount, 2) }}</h5>
                                </div>
                                <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Projects Section -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Projects</h5>
                    <a href="{{ route('admin.projects.create') }}?client_id={{ $clientId }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Project
                    </a>
                </div>
                <div>
                    @if($projects->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-stripped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                  <th><i class="fas fa-code"></i> Code</th>
                                <th><i class="fas fa-user"></i> Name</th>
                                <th><i class="fas fa-money-bill-wave"></i> Amount</th>
                                <th><i class="fas fa-toggle-on"></i> Status</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $project)
                                    <tr>
                                        <td><code>{{ $project->project_code }}</code></td>
                                        <td>{{ $project->name }}</td>
                                        <td>${{ number_format($project->total_amount, 2) }}</td>
                                        <td>
                                            @if($project->status == 'pending')
                                                <span class="badge bg-warning">Pending</span>
                                            @elseif($project->status == 'in_progress')
                                                <span class="badge bg-info">In Progress</span>
                                            @elseif($project->status == 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($project->status == 'on_hold')
                                                <span class="badge bg-secondary">On Hold</span>
                                            @else
                                                <span class="badge bg-danger">Cancelled</span>
                                            @endif
                                         </td>
                                        <td>
                                            <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                         </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-project-diagram fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No projects yet</p>
                            <a href="{{ route('admin.projects.create') }}?client_id={{ $clientId }}" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i> Add First Project
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Estimates Section -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Estimates</h5>
                    <a href="{{ route('admin.estimates.create') }}?client_id={{ $clientId }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Create Estimate
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($estimates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i> </th>
                                        <th><i class="fas fa-dollar-sign me-1"></i> Total</th>
                                        <th><i class="fas fa-check-circle me-1"></i> Status</th>
                                        <th><i class="fas fa-calendar-alt me-1"></i> Date</th>
                                        <th><i class="fas fa-ellipsis-h me-1"></i> Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($estimates as $estimate)
                                    <tr>
                                        <td><code>{{ $estimate->estimate_number }}</code></td>
                                        <td>${{ number_format($estimate->total, 2) }}</td>
                                        <td>
                                            @if($estimate->status == 'draft')
                                                <span class="badge bg-secondary">Draft</span>
                                            @elseif($estimate->status == 'sent')
                                                <span class="badge bg-info">Sent</span>
                                            @elseif($estimate->status == 'accepted')
                                                <span class="badge bg-success">Accepted</span>
                                            @elseif($estimate->status == 'rejected')
                                                <span class="badge bg-danger">Rejected</span>
                                            @else
                                                <span class="badge bg-warning">Expired</span>
                                            @endif
                                         </td>
                                        <td>{{ $estimate->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.estimates.show', $estimate->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                         </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No estimates yet</p>
                            <a href="{{ route('admin.estimates.create') }}?client_id={{ $clientId }}" class="btn btn-sm btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i> Create First Estimate
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
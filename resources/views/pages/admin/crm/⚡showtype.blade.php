<?php

use Livewire\Component;
use App\Models\ClientType;

new class extends Component
{
    public $typeId;
    public $type = null;
    
    public function mount($id)
    {
        $this->typeId = $id;
        $this->loadType();
    }
    
    public function loadType()
    {
        $this->type = ClientType::find($this->typeId);
        
        if (!$this->type) {
            session()->flash('error', 'Client Type not found!');
            return redirect()->route('admin.client-types');
        }
    }
    
    public function backToList()
    {
        return redirect()->route('admin.client-types');
    }
    
    public function editType()
    {
        return redirect()->route('admin.client-types.edit', $this->typeId);
    }
    
    public function deleteType()
    {
        $this->type->delete();
        session()->flash('message', 'Client Type deleted successfully!');
        return redirect()->route('admin.client-types');
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
    
    @if($type)
    <div>
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Client Type Details</h2>
                <p class="text-muted">View complete information about client type</p>
            </div>
            <div>
                <button wire:click="backToList" class="btn btn-secondary rounded-3 me-2">
                    <i class="fas fa-arrow-left me-2"></i> Back to List
                </button>
                <button wire:click="editType" class="btn btn-info rounded-3 me-2">
                    <i class="fas fa-edit me-2"></i> Edit Type
                </button>
                <button wire:click="deleteType" class="btn btn-danger rounded-3" onclick="return confirm('Are you sure you want to delete this client type?')">
                    <i class="fas fa-trash-alt me-2"></i> Delete
                </button>
            </div>
        </div>
        
        <!-- Main Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-gradient-primary text-white rounded-top-4">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i> Client Type Information
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150" class="fw-bold text-muted">ID:</td>
                                <td><span class="badge bg-secondary">#{{ $type->id }}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Name:</td>
                                <td><strong class="fs-5">{{ $type->name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Slug:</td>
                                <td><code>{{ $type->slug }}</code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Icon:</td>
                                <td>
                                    @if($type->icon)
                                        <i class="{{ $type->icon }} fa-2x text-primary"></i>
                                        <span class="ms-2">{{ $type->icon }}</span>
                                    @else
                                        <span class="text-muted">No icon set</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Color:</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle" style="width: 30px; height: 30px; background-color: {{ $type->color }};"></span>
                                        <span>{{ $type->color }}</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="150" class="fw-bold text-muted">Display Order:</td>
                                <td>{{ $type->order }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Status:</td>
                                <td>
                                    @if($type->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Created At:</td>
                                <td>{{ \Carbon\Carbon::parse($type->created_at)->format('F d, Y h:i A') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Last Updated:</td>
                                <td>{{ \Carbon\Carbon::parse($type->updated_at)->format('F d, Y h:i A') }}</td>
                            </tr>
                            @if($type->deleted_at)
                            <tr>
                                <td class="fw-bold text-muted">Deleted At:</td>
                                <td class="text-danger">{{ \Carbon\Carbon::parse($type->deleted_at)->format('F d, Y h:i A') }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                <!-- Description -->
                @if($type->description)
                <div class="mt-3">
                    <hr>
                    <h6 class="fw-bold text-muted mb-2">
                        <i class="fas fa-align-left me-2"></i> Description
                    </h6>
                    <p class="mb-0">{{ $type->description }}</p>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Stats Card -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-light rounded-top-4">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i> Statistics
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="row text-center">
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="p-3 bg-light rounded-3">
                            <i class="fas fa-users fa-3x text-primary mb-2"></i>
                            <h3 class="mb-0">{{ $type->leads()->count() ?? 0 }}</h3>
                            <p class="text-muted mb-0">Total Leads</p>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="p-3 bg-light rounded-3">
                            <i class="fas fa-check-circle fa-3x text-success mb-2"></i>
                            <h3 class="mb-0">{{ $type->leads()->where('status', 'won')->count() ?? 0 }}</h3>
                            <p class="text-muted mb-0">Won Deals</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <i class="fas fa-clock fa-3x text-warning mb-2"></i>
                            <h3 class="mb-0">{{ $type->leads()->where('status', 'pending')->count() ?? 0 }}</h3>
                            <p class="text-muted mb-0">Pending Leads</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Leads (Optional - will work when leads table is created) -->
        @if($type->leads && $type->leads()->count() > 0)
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-light rounded-top-4">
                <h5 class="mb-0">
                    <i class="fas fa-history text-primary me-2"></i> Recent Leads
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Company Name</th>
                                <th class="px-4">Contact Person</th>
                                <th class="px-4">Status</th>
                                <th class="px-4">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($type->leads()->latest()->take(5)->get() as $lead)
                            <tr>
                                <td class="px-4">{{ $lead->company_name }}</td>
                                <td class="px-4">{{ $lead->contact_person ?? '-' }}</td>
                                <td class="px-4">
                                    <span class="badge bg-info">{{ $lead->status }}</span>
                                </td>
                                <td class="px-4">{{ $lead->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
    @else
    <div class="text-center py-5">
        <i class="fas fa-building fa-4x text-muted mb-3"></i>
        <h3>Client Type Not Found</h3>
        <p class="text-muted">The client type you're looking for doesn't exist or has been deleted.</p>
        <a href="{{ route('admin.client-types') }}" class="btn btn-primary rounded-3 mt-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Client Types
        </a>
    </div>
    @endif
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .table-borderless td {
        padding: 10px 0;
    }
    .btn-info {
        background: #0dcaf0;
        border: none;
        transition: transform 0.2s;
    }
    .btn-info:hover {
        background: #0bb5d8;
        transform: translateY(-2px);
    }
    .btn-danger {
        transition: transform 0.2s;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
    }
    .badge {
        padding: 6px 12px;
    }
</style>
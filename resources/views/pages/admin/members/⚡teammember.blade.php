<?php

use Livewire\Component;
use App\Models\TeamMember;
use App\Models\Designation;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    
    // Filters
    public $search = '';
    public $designation_filter = '';
    public $status_filter = '';
    public $perPage = 10;
    
    protected $queryString = ['search', 'designation_filter', 'status_filter'];
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedDesignationFilter()
    {
        $this->resetPage();
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->search = '';
        $this->designation_filter = '';
        $this->status_filter = '';
        $this->perPage = 10;
        $this->resetPage();
    }
    
    public function toggleStatus($id)
    {
        $member = TeamMember::find($id);
        if ($member) {
            $member->is_active = !$member->is_active;
            $member->save();
            session()->flash('message', 'Member status updated successfully!');
        }
    }
    
    public function deleteMember($id)
    {
        $member = TeamMember::find($id);
        if ($member) {
            // Delete avatar if exists
            if ($member->avatar && \Storage::disk('public')->exists($member->avatar)) {
                \Storage::disk('public')->delete($member->avatar);
            }
            $member->delete();
            session()->flash('message', 'Team member deleted successfully!');
        }
    }
    
    // Get all designations for filter dropdown
    public function getDesignationsProperty()
    {
        return Designation::where('is_active', true)->orderBy('name')->get();
    }
    
    // Get all team members with filters
    public function getMembersProperty()
    {
        $query = TeamMember::with('designation');
        
        // Search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%')
                  ->orWhere('phone', 'like', '%'.$this->search.'%');
            });
        }
        
        // Designation filter
        if (!empty($this->designation_filter)) {
            $query->where('designation_id', $this->designation_filter);
        }
        
        // Status filter
        if ($this->status_filter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status_filter === 'inactive') {
            $query->where('is_active', false);
        }
        
        return $query->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($this->perPage);
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
            <h2 class="fw-bold mb-0"><i class="fas fa-users me-2"></i>Team Members</h2>
            <p class="text-muted">Manage your team members and their information</p>
        </div>
        <div>
            <a href="{{ route('admin.team.members.create') }}" class="btn btn-primary rounded-3">
                <i class="fas fa-plus-circle me-2"></i> Add New Member
            </a>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters Row -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" 
                               placeholder="Search by name, email or phone...">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <select wire:model.live="designation_filter" class="form-select rounded-3">
                        <option value="">All Designations</option>
                        @foreach($this->designations as $designation)
                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select wire:model.live="status_filter" class="form-select rounded-3">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <select wire:model.live="perPage" class="form-select rounded-3">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    @if($search || $designation_filter || $status_filter)
                        <button wire:click="resetFilters" class="btn btn-secondary rounded-3 w-100" title="Reset Filters">
                            <i class="fas fa-undo-alt"></i>
                        </button>
                    @endif
                </div>
            </div>
            
            <!-- Members Grid/Card View -->
            <div class="row g-4">
                @forelse($this->members as $member)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <!-- Member Avatar -->
                            <div class="text-center pt-4">
                                @if($member->avatar)
                                    <img src="{{ asset('storage/' . $member->avatar) }}" 
                                         class="rounded-circle" 
                                         style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #FE171B;">
                                @else
                                    <div class="rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center mx-auto"
                                         style="width: 100px; height: 100px;">
                                        <span class="text-white fs-1 fw-bold">
                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Member Info -->
                            <div class="card-body text-center">
                                <h5 class="fw-bold mb-1">{{ $member->name }}</h5>
                                <span class="badge mb-2" style="background-color: {{ $member->designation->color ?? '#6c757d' }}; color: white;">
                                    <i class="{{ $member->designation->icon ?? 'fas fa-tag' }} me-1"></i>
                                    {{ $member->designation->name ?? 'No Designation' }}
                                </span>
                                
                                <div class="mt-2">
                                    <p class="mb-1">
                                        <i class="fas fa-envelope text-muted me-2"></i>
                                        <small>{{ $member->email }}</small>
                                    </p>
                                    @if($member->phone)
                                        <p class="mb-1">
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            <small>{{ $member->phone }}</small>
                                        </p>
                                    @endif
                                    @if($member->experience_years > 0)
                                        <p class="mb-1">
                                            <i class="fas fa-briefcase text-muted me-2"></i>
                                            <small>{{ $member->experience_years }} years experience</small>
                                        </p>
                                    @endif
                                </div>
                                
                                <!-- Skills -->
                                @if($member->skills)
                                    <div class="mt-2 d-flex flex-wrap gap-1 justify-content-center">
                                        @php
                                            $skills = is_array($member->skills) ? $member->skills : json_decode($member->skills, true);
                                        @endphp
                                        @if($skills)
                                            @foreach(array_slice($skills, 0, 3) as $skill)
                                                <span class="badge bg-light text-dark">{{ $skill }}</span>
                                            @endforeach
                                            @if(count($skills) > 3)
                                                <span class="badge bg-light text-dark">+{{ count($skills) - 3 }}</span>
                                            @endif
                                        @endif
                                    </div>
                                @endif
                                
                                <!-- Social Links -->
                                <div class="mt-3 d-flex justify-content-center gap-2">
                                    @if($member->facebook)
                                        <a href="{{ $member->facebook }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 30px; height: 30px; padding: 0;">
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                    @endif
                                    @if($member->twitter)
                                        <a href="{{ $member->twitter }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle" style="width: 30px; height: 30px; padding: 0;">
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    @endif
                                    @if($member->linkedin)
                                        <a href="{{ $member->linkedin }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle" style="width: 30px; height: 30px; padding: 0;">
                                            <i class="fab fa-linkedin-in"></i>
                                        </a>
                                    @endif
                                    @if($member->instagram)
                                        <a href="{{ $member->instagram }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 30px; height: 30px; padding: 0;">
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    @endif
                                    @if($member->github)
                                        <a href="{{ $member->github }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-circle" style="width: 30px; height: 30px; padding: 0;">
                                            <i class="fab fa-github"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Card Footer with Actions -->
                            <div class="card-footer bg-transparent border-top-0 pb-4">
                                <div class="d-flex justify-content-center gap-2">
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $member->id }})" {{ $member->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label small">Active</label>
                                    </div>
                                    <a href="{{ route('admin.team.members.edit', $member->id) }}" class="btn btn-sm btn-info rounded-3">
                                        <i class="fas fa-edit"></i> 
                                    </a>
                                    <a href="{{ route('admin.team.members.show', $member->id) }}" class="btn btn-sm btn-warning rounded-3">
                                        <i class="fas fa-eye"></i> 
                                    </a>
                                    <button wire:click="deleteMember({{ $member->id }})" class="btn btn-sm btn-danger rounded-3" onclick="return confirm('Are you sure you want to delete this team member?')">
                                        <i class="fas fa-trash-alt"></i> 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No team members found</h4>
                            <p class="text-muted">Click "Add New Member" to create your first team member.</p>
                            <a href="{{ route('admin.team.members.create') }}" class="btn btn-primary rounded-3 mt-2">
                                <i class="fas fa-plus-circle me-2"></i> Add New Member
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $this->members->links() }}
            </div>
            
            <!-- Stats -->
            <div class="mt-3 text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Showing {{ $this->members->firstItem() ?? 0 }} to {{ $this->members->lastItem() ?? 0 }} of {{ $this->members->total() }} team members
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
    }
    .btn-info, .btn-danger {
        transition: transform 0.2s;
    }
    .btn-info:hover, .btn-danger:hover {
        transform: translateY(-2px);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .form-check-input:checked {
        background-color: #FE171B;
        border-color: #FE171B;
    }
</style>
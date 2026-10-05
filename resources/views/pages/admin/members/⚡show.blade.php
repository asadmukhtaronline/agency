<?php

use Livewire\Component;
use App\Models\TeamMember;

new class extends Component
{
    public $memberId;
    public $member;
    
    public function mount($id)
    {
        $this->memberId = $id;
        $this->loadMember();
    }
    
    public function loadMember()
    {
        $this->member = TeamMember::with('designation')->find($this->memberId);
        
        if (!$this->member) {
            session()->flash('error', 'Team member not found!');
            return redirect()->route('admin.team.members');
        }
    }
    
    public function deleteMember()
    {
        if ($this->member) {
            // Delete avatar if exists
            if ($this->member->avatar && \Storage::disk('public')->exists($this->member->avatar)) {
                \Storage::disk('public')->delete($this->member->avatar);
            }
            $this->member->delete();
            session()->flash('message', 'Team member deleted successfully!');
            return redirect()->route('admin.team.members');
        }
    }
    
    public function getSkillsArrayProperty()
    {
        if (is_array($this->member->skills)) {
            return $this->member->skills;
        }
        return json_decode($this->member->skills, true) ?? [];
    }
    
    public function getSocialLinksProperty()
    {
        $links = [];
        if ($this->member->facebook) $links['facebook'] = $this->member->facebook;
        if ($this->member->twitter) $links['twitter'] = $this->member->twitter;
        if ($this->member->linkedin) $links['linkedin'] = $this->member->linkedin;
        if ($this->member->instagram) $links['instagram'] = $this->member->instagram;
        if ($this->member->github) $links['github'] = $this->member->github;
        return $links;
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
    
    @if($member)
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="fas fa-user-circle me-2"></i>Team Member Details</h2>
            <p class="text-muted">Complete profile information</p>
        </div>
        <div>
            <a href="{{ route('admin.team.members') }}" class="btn btn-secondary rounded-3 me-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Members
            </a>
            <a href="{{ route('admin.team.members.edit', $member->id) }}" class="btn btn-info rounded-3 me-2">
                <i class="fas fa-edit me-2"></i> Edit Member
            </a>
            <button wire:click="deleteMember" class="btn btn-danger rounded-3" onclick="return confirm('Are you sure you want to delete this team member?')">
                <i class="fas fa-trash-alt me-2"></i> Delete
            </button>
        </div>
    </div>
    
    <div class="row">
        <!-- Left Column - Profile -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 text-center">
                    <!-- Avatar -->
                    @if($member->avatar)
                        <img src="{{ asset('storage/' . $member->avatar) }}" 
                             class="rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #FE171B;">
                    @else
                        <div class="rounded-circle bg-gradient-primary d-flex align-items-center justify-content-center mx-auto mb-3"
                             style="width: 150px; height: 150px;">
                            <span class="text-white display-1 fw-bold">
                                {{ strtoupper(substr($member->name, 0, 1)) }}
                            </span>
                        </div>
                    @endif
                    
                    <h3 class="fw-bold mb-1">{{ $member->name }}</h3>
                    <span class="badge mb-2 px-3 py-2" style="background-color: {{ $member->designation->color ?? '#6c757d' }}; color: white; font-size: 12px;">
                        <i class="{{ $member->designation->icon ?? 'fas fa-tag' }} me-1"></i>
                        {{ $member->designation->name ?? 'No Designation' }}
                    </span>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-center gap-3 mb-2">
                            @if($member->is_active)
                                <span class="badge bg-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-danger px-3 py-2">
                                    <i class="fas fa-times-circle me-1"></i> Inactive
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Social Links -->
                    @if($this->socialLinks)
                        <hr>
                        <h6 class="fw-bold mb-3"><i class="fas fa-share-alt me-2"></i>Connect</h6>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            @if($member->facebook)
                                <a href="{{ $member->facebook }}" target="_blank" class="btn btn-outline-primary rounded-circle" style="width: 35px; height: 35px; padding: 0;">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            @endif
                            @if($member->twitter)
                                <a href="{{ $member->twitter }}" target="_blank" class="btn btn-outline-info rounded-circle" style="width: 35px; height: 35px; padding: 0;">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            @endif
                            @if($member->linkedin)
                                <a href="{{ $member->linkedin }}" target="_blank" class="btn btn-outline-primary rounded-circle" style="width: 35px; height: 35px; padding: 0;">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            @endif
                            @if($member->instagram)
                                <a href="{{ $member->instagram }}" target="_blank" class="btn btn-outline-danger rounded-circle" style="width: 35px; height: 35px; padding: 0;">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            @endif
                            @if($member->github)
                                <a href="{{ $member->github }}" target="_blank" class="btn btn-outline-dark rounded-circle" style="width: 35px; height: 35px; padding: 0;">
                                    <i class="fab fa-github"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Right Column - Details -->
        <div class="col-md-8">
            <!-- Contact Information -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-address-card text-primary me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold">Email Address</label>
                                <p class="mb-0">
                                    <i class="fas fa-envelope me-2"></i>
                                    <a href="mailto:{{ $member->email }}">{{ $member->email }}</a>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold">Phone Number</label>
                                <p class="mb-0">
                                    <i class="fas fa-phone me-2"></i>
                                    <a href="tel:{{ $member->phone }}">{{ $member->phone ?? 'Not provided' }}</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Professional Information -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-briefcase text-primary me-2"></i>Professional Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold">Designation</label>
                                <p class="mb-0">
                                    <i class="fas fa-tag me-2"></i>
                                    {{ $member->designation->name ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold">Experience</label>
                                <p class="mb-0">
                                    <i class="fas fa-clock me-2"></i>
                                    {{ $member->experience_years > 0 ? $member->experience_years . ' years' : 'Fresher' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-muted small fw-bold">Education</label>
                        <p class="mb-0">
                            <i class="fas fa-graduation-cap me-2"></i>
                            {{ $member->education ?? 'Not provided' }}
                        </p>
                    </div>
                    
                    @if($this->skillsArray && count($this->skillsArray) > 0)
                        <div class="mb-3">
                            <label class="text-muted small fw-bold">Skills</label>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                @foreach($this->skillsArray as $skill)
                                    <span class="badge bg-light text-dark border px-3 py-2">
                                        <i class="fas fa-code me-1"></i> {{ trim($skill) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    @if($member->achievements)
                        <div class="mb-3">
                            <label class="text-muted small fw-bold">Achievements</label>
                            <p class="mb-0">
                                <i class="fas fa-trophy me-2 text-warning"></i>
                                {{ $member->achievements }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Bio -->
            @if($member->bio)
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-light rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-align-left text-primary me-2"></i>Biography</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-0">{{ $member->bio }}</p>
                    </div>
                </div>
            @endif
            
            <!-- Additional Info -->
            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-info-circle text-primary me-2"></i>Additional Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Display Order</label>
                            <p class="mb-0">{{ $member->order }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Member Since</label>
                            <p class="mb-0">{{ $member->created_at->format('F d, Y') }}</p>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label class="text-muted small fw-bold">Last Updated</label>
                            <p class="mb-0">{{ $member->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    .btn-info, .btn-danger, .btn-secondary {
        transition: transform 0.2s;
    }
    .btn-info:hover, .btn-danger:hover, .btn-secondary:hover {
        transform: translateY(-2px);
    }
    .badge {
        font-weight: 500;
    }
    .card-header {
        border-bottom: none;
    }
</style>
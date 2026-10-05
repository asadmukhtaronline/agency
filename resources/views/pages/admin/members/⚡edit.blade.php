<?php

use Livewire\Component;
use App\Models\TeamMember;
use App\Models\Designation;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    public $memberId;
    public $member;
    
    // Basic Information
    public $designation_id = '';
    public $name = '';
    public $email = '';
    public $phone = '';
    public $avatar = null;
    public $existingAvatar = '';
    public $bio = '';
    public $skills = '';
    public $experience_years = '';
    public $education = '';
    public $achievements = '';
    public $order = 0;
    public $is_active = true;
    
    // Social Media Links
    public $facebook = '';
    public $twitter = '';
    public $linkedin = '';
    public $instagram = '';
    public $github = '';
    
    public function mount($id)
    {
        $this->memberId = $id;
        $this->loadMember();
    }
    
    public function loadMember()
    {
        $this->member = TeamMember::find($this->memberId);
        
        if (!$this->member) {
            session()->flash('error', 'Team member not found!');
            return redirect()->route('admin.team.members');
        }
        
        // Fill form with member data
        $this->designation_id = $this->member->designation_id;
        $this->name = $this->member->name;
        $this->email = $this->member->email;
        $this->phone = $this->member->phone;
        $this->existingAvatar = $this->member->avatar;
        $this->bio = $this->member->bio;
        
        // Convert skills array to comma separated string
        $skillsArray = is_array($this->member->skills) ? $this->member->skills : json_decode($this->member->skills, true);
        $this->skills = is_array($skillsArray) ? implode(', ', $skillsArray) : '';
        
        $this->experience_years = $this->member->experience_years;
        $this->education = $this->member->education;
        $this->achievements = $this->member->achievements;
        $this->order = $this->member->order;
        $this->is_active = $this->member->is_active;
        
        // Social Media
        $this->facebook = $this->member->facebook;
        $this->twitter = $this->member->twitter;
        $this->linkedin = $this->member->linkedin;
        $this->instagram = $this->member->instagram;
        $this->github = $this->member->github;
    }
    
    public function updatedSkills($value)
    {
        // Keep as string for form binding
        $this->skills = $value;
    }
    
    public function removeAvatar()
    {
        if ($this->existingAvatar && \Storage::disk('public')->exists($this->existingAvatar)) {
            \Storage::disk('public')->delete($this->existingAvatar);
        }
        $this->existingAvatar = '';
        $this->avatar = null;
        session()->flash('message', 'Avatar removed successfully!');
    }
    
    public function save()
    {
        $this->validate([
            'designation_id' => 'required|exists:designations,id',
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:team_members,email,' . $this->memberId,
            'phone' => 'nullable|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'bio' => 'nullable|max:500',
            'skills' => 'nullable|max:500',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'education' => 'nullable|max:255',
            'achievements' => 'nullable|max:500',
            'order' => 'nullable|integer',
            'is_active' => 'boolean',
            'facebook' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'github' => 'nullable|url|max:255',
        ]);
        
        // Handle avatar upload
        $avatarPath = $this->existingAvatar;
        if ($this->avatar) {
            if ($this->existingAvatar && \Storage::disk('public')->exists($this->existingAvatar)) {
                \Storage::disk('public')->delete($this->existingAvatar);
            }
            $avatarPath = $this->avatar->store('team-avatars', 'public');
        }
        
        // Convert skills to JSON
        $skillsArray = [];
        if (!empty($this->skills)) {
            $skillsArray = array_map('trim', explode(',', $this->skills));
        }
        
        // Update team member
        $member = TeamMember::find($this->memberId);
        $member->update([
            'designation_id' => $this->designation_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $avatarPath,
            'bio' => $this->bio,
            'skills' => json_encode($skillsArray),
            'experience_years' => $this->experience_years ?: 0,
            'education' => $this->education,
            'achievements' => $this->achievements,
            'order' => $this->order ?: 0,
            'is_active' => $this->is_active,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            'instagram' => $this->instagram,
            'github' => $this->github,
        ]);
        
        session()->flash('message', 'Team member updated successfully!');
        return redirect()->route('admin.team.members');
    }
    
    public function getDesignationsProperty()
    {
        return Designation::where('is_active', true)->orderBy('order', 'asc')->orderBy('name', 'asc')->get();
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
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            Please fix the following errors:
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="fas fa-user-edit me-2"></i>Edit Team Member</h2>
            <p class="text-muted">Update team member information</p>
        </div>
        <div>
            <a href="{{ route('admin.team.members') }}" class="btn btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Members
            </a>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Member: {{ $name }}</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-user text-primary me-2"></i> Basic Information
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" 
                                   wire:model="name" placeholder="e.g., John Doe">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control rounded-3 @error('email') is-invalid @enderror" 
                                   wire:model="email" placeholder="john@example.com">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" class="form-control rounded-3 @error('phone') is-invalid @enderror" 
                                   wire:model="phone" placeholder="+92 300 1234567">
                            @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 @error('designation_id') is-invalid @enderror" wire:model="designation_id">
                                <option value="">Select Designation</option>
                                @foreach($this->designations as $designation)
                                    <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                @endforeach
                            </select>
                            @error('designation_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Profile Avatar</label>
                            @if($existingAvatar && !$avatar)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $existingAvatar) }}" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="mt-1">
                                        <button type="button" class="btn btn-sm btn-danger" wire:click="removeAvatar">
                                            <i class="fas fa-trash-alt me-1"></i> Remove Avatar
                                        </button>
                                    </div>
                                </div>
                            @endif
                            <input type="file" class="form-control rounded-3 @error('avatar') is-invalid @enderror" 
                                   wire:model="avatar" accept="image/*">
                            @error('avatar') <span class="text-danger small">{{ $message }}</span> @enderror
                            @if($avatar)
                                <div class="mt-2">
                                    <img src="{{ $avatar->temporaryUrl() }}" class="rounded-3" style="width: 80px; height: 80px; object-fit: cover;">
                                    <small class="text-muted d-block">New image preview</small>
                                </div>
                            @endif
                            <small class="text-muted">Max size: 2MB (JPG, PNG, GIF, WEBP)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Short Bio</label>
                            <textarea class="form-control rounded-3 @error('bio') is-invalid @enderror" 
                                      wire:model="bio" rows="3" placeholder="Brief introduction about the team member..."></textarea>
                            @error('bio') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-chart-line text-primary me-2"></i> Professional Information
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Skills</label>
                            <input type="text" class="form-control rounded-3 @error('skills') is-invalid @enderror" 
                                   wire:model="skills" placeholder="Laravel, PHP, JavaScript, React">
                            @error('skills') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">Separate skills with commas</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Experience Years</label>
                            <input type="number" class="form-control rounded-3 @error('experience_years') is-invalid @enderror" 
                                   wire:model="experience_years" placeholder="e.g., 5" min="0" max="50">
                            @error('experience_years') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Education</label>
                            <input type="text" class="form-control rounded-3 @error('education') is-invalid @enderror" 
                                   wire:model="education" placeholder="BS Computer Science, University of XYZ">
                            @error('education') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Achievements</label>
                            <textarea class="form-control rounded-3 @error('achievements') is-invalid @enderror" 
                                      wire:model="achievements" rows="3" placeholder="Notable achievements, awards, certifications..."></textarea>
                            @error('achievements') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Display Order</label>
                            <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label fw-semibold" for="is_active">
                                    <i class="fas fa-check-circle text-success me-1"></i> Active (Visible on website)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media Section -->
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-share-alt text-primary me-2"></i> Social Media Links
                        </h6>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold"><i class="fab fa-facebook text-primary me-1"></i> Facebook</label>
                        <input type="url" class="form-control rounded-3" wire:model="facebook" placeholder="https://facebook.com/username">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold"><i class="fab fa-twitter text-info me-1"></i> Twitter</label>
                        <input type="url" class="form-control rounded-3" wire:model="twitter" placeholder="https://twitter.com/username">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold"><i class="fab fa-linkedin text-primary me-1"></i> LinkedIn</label>
                        <input type="url" class="form-control rounded-3" wire:model="linkedin" placeholder="https://linkedin.com/in/username">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold"><i class="fab fa-instagram text-danger me-1"></i> Instagram</label>
                        <input type="url" class="form-control rounded-3" wire:model="instagram" placeholder="https://instagram.com/username">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold"><i class="fab fa-github text-dark me-1"></i> GitHub</label>
                        <input type="url" class="form-control rounded-3" wire:model="github" placeholder="https://github.com/username">
                    </div>
                </div>
                
                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                    <a href="{{ route('admin.team.members') }}" class="btn btn-secondary px-4 py-2 rounded-3">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Update Member
                    </button>
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }
    .btn-sm {
        padding: 0.2rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
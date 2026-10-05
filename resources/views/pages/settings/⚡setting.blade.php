<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;
    
    // Profile fields
    public $name = '';
    public $email = '';
    public $phone = '';
    public $bio = '';
    public $avatar = null;
    public $existingAvatar = '';
    
    // Password fields
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';
    
    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->bio = $user->bio;
        $this->existingAvatar = $user->avatar;
    }
    
    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|max:50',
            'bio' => 'nullable|max:500',
            'avatar' => 'nullable|image|max:2048',
        ]);
        
        $user = Auth::user();
        
        // Handle avatar upload
        $avatarPath = $this->existingAvatar;
        if ($this->avatar) {
            if ($this->existingAvatar && Storage::disk('public')->exists($this->existingAvatar)) {
                Storage::disk('public')->delete($this->existingAvatar);
            }
            $avatarPath = $this->avatar->store('avatars', 'public');
        }
        
        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'avatar' => $avatarPath,
        ]);
        
        session()->flash('profile_success', 'Profile updated successfully!');
    }
    
    public function removeAvatar()
    {
        if ($this->existingAvatar && Storage::disk('public')->exists($this->existingAvatar)) {
            Storage::disk('public')->delete($this->existingAvatar);
        }
        $this->existingAvatar = '';
        $this->avatar = null;
        
        $user = Auth::user();
        $user->update(['avatar' => null]);
        
        session()->flash('profile_success', 'Avatar removed successfully!');
    }
    
    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ]);
        
        $user = Auth::user();
        
        // Check current password
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Current password is incorrect.');
            return;
        }
        
        // Update password
        $user->update([
            'password' => Hash::make($this->new_password),
        ]);
        
        // Reset password fields
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        
        session()->flash('password_success', 'Password changed successfully!');
    }
    
    public function getAvatarUrlProperty()
    {
        if ($this->existingAvatar) {
            return asset('storage/' . $this->existingAvatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=FE171B&color=fff&size=100';
    }
};

?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Profile Settings</h2>
            <p class="text-muted">Manage your account settings and preferences</p>
        </div>
    </div>
    
    @if(session()->has('profile_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('profile_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session()->has('password_success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('password_success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="row">
        <!-- Profile Information -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user-circle me-2 text-primary"></i>Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="updateProfile">
                        <!-- Avatar -->
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" class="rounded-circle" width="100" height="100" style="object-fit: cover; border: 3px solid #FE171B;">
                                @elseif($existingAvatar)
                                    <img src="{{ asset('storage/' . $existingAvatar) }}" class="rounded-circle" width="100" height="100" style="object-fit: cover; border: 3px solid #FE171B;">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($name) }}&background=FE171B&color=fff&size=100" class="rounded-circle" width="100" height="100" style="border: 3px solid #FE171B;">
                                @endif
                                <div class="position-absolute bottom-0 end-0">
                                    <label class="btn btn-sm btn-primary rounded-circle mb-0" style="width: 32px; height: 32px; cursor: pointer;">
                                        <i class="fas fa-camera fa-xs"></i>
                                        <input type="file" wire:model="avatar" class="d-none" accept="image/*">
                                    </label>
                                </div>
                            </div>
                            @if($existingAvatar)
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" wire:click="removeAvatar">
                                        <i class="fas fa-trash-alt me-1"></i> Remove Avatar
                                    </button>
                                </div>
                            @endif
                            @error('avatar') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" wire:model="phone">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bio / About</label>
                            <textarea class="form-control" wire:model="bio" rows="3" placeholder="Tell us about yourself..."></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Change Password -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-key me-2 text-primary"></i>Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="updatePassword">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" wire:model="current_password">
                            @error('current_password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror" wire:model="new_password">
                            @error('new_password') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" wire:model="new_password_confirmation">
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Password must be at least 8 characters long and strong.</small>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Account Information -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th width="40%">Account Type</th>
                            <td>
                                <span class="badge bg-{{ auth()->user()->role_badge_class }}">
                                    {{ auth()->user()->role_label }}
                                </span>
                             </td
                        </tr>
                        <tr>
                            <th>Member Since</th>
                            <td>{{ auth()->user()->created_at->format('F d, Y') }}</td
                        </tr>
                        <tr>
                            <th>Last Login</th>
                            <td>
                                @if(auth()->user()->last_login_at)
                                    {{ auth()->user()->last_login_at->format('F d, Y h:i A') }}
                                    <br><small class="text-muted">{{ auth()->user()->last_login_at->diffForHumans() }}</small>
                                @else
                                    Never
                                @endif
                             </td
                        </tr>
                        <tr>
                            <th>Last Login IP</th>
                            <td>{{ auth()->user()->last_login_ip ?? 'N/A' }}</td
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    .card-header {
        border-bottom: 1px solid #dee2e6;
        background: white;
    }
    .form-control:focus {
        border-color: #FE171B;
        box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
    }
    .btn-primary {
        background-color: #FE171B;
        border-color: #FE171B;
    }
    .btn-primary:hover {
        background-color: #cc1215;
        border-color: #cc1215;
    }
    .btn-outline-primary {
        border-color: #FE171B;
        color: #FE171B;
    }
    .btn-outline-primary:hover {
        background-color: #FE171B;
        border-color: #FE171B;
    }
</style>
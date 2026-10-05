<?php

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;
    
    public $userId;
    public $user;
    
    // Form fields
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = '';
    public $phone = '';
    public $bio = '';
    public $avatar = null;
    public $existingAvatar = '';
    public $is_active = true;
    
    public function mount($id)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
        
        $this->userId = $id;
        $this->loadUser();
    }
    
    public function loadUser()
    {
        $this->user = User::find($this->userId);
        
        if (!$this->user) {
            session()->flash('error', 'User not found!');
            return redirect()->route('admin.team.users.index');
        }
        
        if ($this->user->isSuperAdmin() && !Auth::user()->isSuperAdmin()) {
            abort(403, 'You cannot edit super admin user.');
        }
        
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->role = $this->user->role;
        $this->phone = $this->user->phone;
        $this->bio = $this->user->bio;
        $this->existingAvatar = $this->user->avatar;
        $this->is_active = $this->user->is_active;
    }
    
    public function getRolesProperty()
    {
        $roles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
        ];
        
        if (!Auth::user()->isSuperAdmin()) {
            unset($roles['super_admin']);
        }
        
        if ($this->user->id == Auth::id()) {
            $roles = [$this->role => $roles[$this->role]];
        }
        
        return $roles;
    }
    
    public function removeAvatar()
    {
        if ($this->existingAvatar && Storage::disk('public')->exists($this->existingAvatar)) {
            Storage::disk('public')->delete($this->existingAvatar);
        }
        $this->existingAvatar = '';
        $this->avatar = null;
        session()->flash('message', 'Avatar removed successfully!');
    }
    
    public function update()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email,' . $this->userId,
            'password' => 'nullable|min:8|confirmed',
            'role' => 'required|in:' . implode(',', array_keys($this->roles)),
            'phone' => 'nullable|max:50',
            'bio' => 'nullable|max:500',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);
        
        $avatarPath = $this->existingAvatar;
        if ($this->avatar) {
            if ($this->existingAvatar && Storage::disk('public')->exists($this->existingAvatar)) {
                Storage::disk('public')->delete($this->existingAvatar);
            }
            $avatarPath = $this->avatar->store('avatars', 'public');
        }
        
        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'avatar' => $avatarPath,
            'is_active' => $this->is_active,
        ];
        
        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }
        
        $user = User::find($this->userId);
        $user->update($updateData);
        
        session()->flash('message', 'User updated successfully!');
        return redirect()->route('admin.team.users.index');
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
            <h2 class="fw-bold mb-0">Edit User</h2>
            <p class="text-muted">Update user information and permissions</p>
        </div>
        <div>
            <a href="{{ route('admin.team.users.index') }}" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Users
            </a>
            <a href="{{ route('admin.team.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i> Add New User
            </a>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Edit User: {{ $name }}</h5>
        </div>
        <div class="card-body">
            <form wire:submit.prevent="update">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" wire:model="email">
                        @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">New Password <span class="text-muted">(Leave blank to keep current)</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" wire:model="password">
                        @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" wire:model="password_confirmation">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" wire:model="role">
                            @foreach($this->roles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                        <small class="text-muted">
                            <strong>Super Admin:</strong> Full access<br>
                            <strong>Admin:</strong> All except user management<br>
                            <strong>Editor:</strong> Create/edit content<br>
                            <strong>Viewer:</strong> Read-only access
                        </small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" wire:model="phone">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Avatar</label>
                    
                    @if($existingAvatar && !$avatar)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $existingAvatar) }}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                            <div class="mt-1">
                                <button type="button" class="btn btn-sm btn-danger" wire:click="removeAvatar">
                                    <i class="fas fa-trash-alt me-1"></i> Remove Avatar
                                </button>
                            </div>
                        </div>
                    @endif
                    
                    <input type="file" class="form-control @error('avatar') is-invalid @enderror" wire:model="avatar" accept="image/*">
                    @error('avatar') <span class="text-danger small">{{ $message }}</span> @enderror
                    
                    @if($avatar)
                        <div class="mt-2">
                            <img src="{{ $avatar->temporaryUrl() }}" class="rounded-circle" width="80" height="80" style="object-fit: cover;">
                            <small class="text-muted d-block">New avatar preview</small>
                        </div>
                    @endif
                    <small class="text-muted">Max: 2MB (JPG, PNG, GIF)</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Bio / About</label>
                    <textarea class="form-control" wire:model="bio" rows="3" placeholder="Short bio about the user..."></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active" {{ $user->id == auth()->id() ? 'disabled' : '' }}>
                            <label class="form-check-label" for="isActive">Active (User can login)</label>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="alert alert-info py-2 mb-0">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                ID: #{{ $userId }} | Created: {{ $user->created_at->format('M d, Y') }}
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="{{ route('admin.team.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
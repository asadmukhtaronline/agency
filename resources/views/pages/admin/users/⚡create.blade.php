<?php

use Livewire\Component;
use App\Models\User;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithFileUploads;
    
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $role = 'admin';
    public $phone = '';
    public $bio = '';
    public $avatar = null;
    public $is_active = true;
    
    public function mount()
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Super admin only.');
        }
    }
    
    public function getRolesProperty()
    {
        return [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'editor' => 'Editor',
            'viewer' => 'Viewer',
        ];
    }
    
    public function save()
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:super_admin,admin,editor,viewer',
            'phone' => 'nullable|max:50',
            'bio' => 'nullable|max:500',
            'avatar' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);
        
        $avatarPath = null;
        if ($this->avatar) {
            $avatarPath = $this->avatar->store('avatars', 'public');
        }
        
        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'avatar' => $avatarPath,
            'is_active' => $this->is_active,
        ]);
        
        session()->flash('message', 'User created successfully!');
        return redirect()->route('admin.team.users.index');
    }
};

?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="fas fa-user-plus me-2"></i>Add New User</h2>
            <p class="text-muted">Create a new system user</p>
        </div>
        <a href="{{ route('admin.team.users.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Users
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>User Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('name') is-invalid @enderror" wire:model="name">
                        @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3 @error('email') is-invalid @enderror" wire:model="email">
                        @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control rounded-3 @error('password') is-invalid @enderror" wire:model="password">
                        @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control rounded-3" wire:model="password_confirmation">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('role') is-invalid @enderror" wire:model="role">
                            @foreach($this->roles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" class="form-control rounded-3" wire:model="phone">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Avatar</label>
                    <input type="file" class="form-control rounded-3" wire:model="avatar" accept="image/*">
                    @error('avatar') <span class="text-danger small">{{ $message }}</span> @enderror
                    @if($avatar)
                        <div class="mt-2">
                            <img src="{{ $avatar->temporaryUrl() }}" class="rounded-circle" width="80" height="80">
                        </div>
                    @endif
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Bio</label>
                    <textarea class="form-control rounded-3" wire:model="bio" rows="3" placeholder="Short bio about the user..."></textarea>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                        <label class="form-check-label fw-semibold" for="isActive">Active (User can login)</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Create User
                    </button>
                    <a href="{{ route('admin.team.users.index') }}" class="btn btn-secondary px-4 py-2 rounded-3">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
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
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
</style>
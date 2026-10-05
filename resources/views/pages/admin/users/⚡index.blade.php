<?php

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $role_filter = '';
    public $status_filter = '';
    public $perPage = 10;
    
    public $showDeleteModal = false;
    public $deleteId = null;
    public $deleteName = null;
    
    protected $queryString = ['search', 'role_filter', 'status_filter'];
    
    public function mount()
    {
        // Check if current user is super admin
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Super admin only.');
        }
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedRoleFilter()
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
        $this->role_filter = '';
        $this->status_filter = '';
        $this->perPage = 10;
        $this->resetPage();
    }
    
    public function confirmDelete($id, $name)
    {
        $this->deleteId = $id;
        $this->deleteName = $name;
        $this->showDeleteModal = true;
    }
    
    public function deleteUser()
    {
        $user = User::find($this->deleteId);
        
        // Prevent deleting yourself
        if ($user->id == Auth::id()) {
            session()->flash('error', 'You cannot delete your own account!');
            $this->showDeleteModal = false;
            return;
        }
        
        // Prevent deleting super admin if current user is not super admin
        if ($user->isSuperAdmin() && !Auth::user()->isSuperAdmin()) {
            session()->flash('error', 'You cannot delete super admin!');
            $this->showDeleteModal = false;
            return;
        }
        
        if ($user) {
            // Delete avatar if exists
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $user->delete();
            session()->flash('message', 'User deleted successfully!');
        }
        
        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteName = null;
    }
    
    public function toggleStatus($id)
    {
        $user = User::find($id);
        
        // Prevent disabling yourself
        if ($user->id == Auth::id()) {
            session()->flash('error', 'You cannot change your own status!');
            return;
        }
        
        $user->is_active = !$user->is_active;
        $user->save();
        session()->flash('message', 'User status updated successfully!');
    }
    
    public function getUsersProperty()
    {
        $query = User::query();
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                  ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }
        
        if ($this->role_filter) {
            $query->where('role', $this->role_filter);
        }
        
        if ($this->status_filter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->status_filter === 'inactive') {
            $query->where('is_active', false);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
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
            <h2 class="fw-bold mb-0">User Management</h2>
            <p class="text-muted">Manage system users and their roles</p>
        </div>
        <a href="{{ route('admin.team.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i> Add New User
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0" placeholder="Search by name or email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select wire:model.live="role_filter" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($this->roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select wire:model.live="status_filter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Per Page</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                        <option value="100">100 per page</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    @if($search || $role_filter || $status_filter)
                        <button wire:click="resetFilters" class="btn btn-secondary w-100">
                            <i class="fas fa-undo-alt"></i> Reset
                        </button>
                    @endif
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->users as $user)
                        <tr>
                            <td>
                                @if($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle" width="35" height="35" style="object-fit: cover;">
                                @else
                                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" style="width: 35px; height: 35px;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <strong>{{ $user->name }}</strong>
                                @if($user->bio)
                                    <br><small class="text-muted">{{ Str::limit($user->bio, 40) }}</small>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleColors = [
                                        'super_admin' => 'danger',
                                        'admin' => 'primary',
                                        'editor' => 'info',
                                        'viewer' => 'secondary',
                                    ];
                                    $roleColor = $roleColors[$user->role] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $roleColor }}">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $user->id }})" {{ $user->is_active ? 'checked' : '' }} {{ $user->id == auth()->id() ? 'disabled' : '' }}>
                                </div>
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <small>{{ $user->last_login_at->format('M d, Y') }}</small>
                                    <br><small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.team.users.edit', $user->id) }}" class="btn btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id != auth()->id())
                                        <button wire:click="confirmDelete({{ $user->id }}, '{{ $user->name }}')" class="btn btn-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                    <h5>No users found</h5>
                                    <p class="text-muted">Click "Add New User" to create your first user.</p>
                                    <a href="{{ route('admin.team.users.create') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Add New User
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $this->users->firstItem() ?? 0 }} to {{ $this->users->lastItem() ?? 0 }} of {{ $this->users->total() }} users
                </div>
                <div>
                    {{ $this->users->links() }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1050;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('showDeleteModal', false)"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                    <h5>Are you sure?</h5>
                    <p>You are about to delete user <strong>{{ $deleteName }}</strong>.</p>
                    <p class="text-muted">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('showDeleteModal', false)">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteUser">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
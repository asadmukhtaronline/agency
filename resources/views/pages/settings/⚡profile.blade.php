<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $user;
    
    public function mount()
    {
        $this->user = Auth::user();
    }
};

?>

<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">My Profile</h2>
            <p class="text-muted">View your profile information</p>
        </div>
        <div>
            <a href="{{ route('admin.user.setting') }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i> Edit Profile
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Avatar -->
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle mb-3" width="120" height="120" style="object-fit: cover; border: 3px solid #FE171B;">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=FE171B&color=fff&size=120" class="rounded-circle mb-3" width="120" height="120">
                    @endif
                    
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <span class="badge bg-{{ $user->role_badge_class }} px-3 py-2 mb-3">
                        <i class="fas fa-shield-alt me-1"></i> {{ $user->role_label }}
                    </span>
                    
                    <div class="mt-3">
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Details -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i> Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th width="180">Full Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email Address</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td>{{ $user->phone ?? 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>
                                <span class="badge bg-{{ $user->role_badge_class }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Bio / About</th>
                            <td>{{ $user->bio ?? 'No bio added yet.' }}</td>
                        </tr>
                        <tr>
                            <th>Member Since</th>
                            <td>{{ $user->created_at->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Last Login</th>
                            <td>
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('F d, Y h:i A') }}
                                    <br><small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                @else
                                    Never
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Last Login IP</th>
                            <td>{{ $user->last_login_ip ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2 text-primary"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('admin.user.setting') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-user-edit me-2"></i> Edit Profile
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('admin.user.setting') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-key me-2"></i> Change Password
                            </a>
                        </div>
                    </div>
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
    .table-borderless td, .table-borderless th {
        padding: 10px 0;
    }
    .btn-outline-primary {
        border-color: #FE171B;
        color: #FE171B;
    }
    .btn-outline-primary:hover {
        background-color: #FE171B;
        border-color: #FE171B;
    }
    .btn-outline-warning {
        border-color: #ffc107;
        color: #856404;
    }
    .btn-outline-warning:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        color: #856404;
    }
</style>
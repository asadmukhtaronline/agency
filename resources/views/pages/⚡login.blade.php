<?php

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;
    
    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        
        $user = \App\Models\User::where('email', $this->email)->first();
        
        if ($user && !$user->is_active) {
            session()->flash('error', 'Your account is deactivated.');
            return;
        }
        
        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            auth()->user()->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);
            session()->flash('message', 'Welcome back!');
            return redirect()->route('admin.dashboard');
        } else {
            session()->flash('error', 'Invalid credentials.');
        }
    }
    
    public function render()
    {
        return <<<'HTML'
        <div class="container">
            <div class="row justify-content-center" style="min-height: 100vh; align-items: center;">
                <div class="col-md-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Login</h4>
                        </div>
                        <div class="card-body">
                            @if(session('message'))
                                <div class="alert alert-success">{{ session('message') }}</div>
                            @endif
                            @if(session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif
                            <form wire:submit.prevent="login">
                                <div class="mb-3">
                                    <label>Email</label>
                                    <input type="email" class="form-control" wire:model="email">
                                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <label>Password</label>
                                    <input type="password" class="form-control" wire:model="password">
                                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" wire:model="remember">
                                    <label class="form-check-label" for="remember">Remember Me</label>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
};
<?php

use Livewire\Component;
use App\Models\WorkType;

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
        $this->type = WorkType::find($this->typeId);
        
        if (!$this->type) {
            session()->flash('error', 'Work Type not found!');
            return redirect()->route('admin.crm.worktype');
        }
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
    
    @if($type)
    <div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-0">Work Type Details</h2>
                <p class="text-muted">View complete information about work type</p>
            </div>
            <div>
                <a href="{{ route('admin.crm.worktype') }}" class="btn btn-secondary rounded-3 me-2">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
                <button wire:click="edit" class="btn btn-info rounded-3 me-2">
                    <i class="fas fa-edit me-2"></i> Edit
                </button>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-gradient-primary text-white rounded-top-4">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Work Type Information</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><td class="fw-bold text-muted">ID:</td><td><span class="badge bg-secondary">#{{ $type->id }}</span></td></tr>
                            <tr><td class="fw-bold text-muted">Name:</td><td><strong class="fs-5">{{ $type->name }}</strong></td></tr>
                            <tr><td class="fw-bold text-muted">Slug:</td><td><code>{{ $type->slug }}</code></td></tr>
                            <tr><td class="fw-bold text-muted">Icon:</td><td>@if($type->icon)<i class="{{ $type->icon }} fa-2x text-primary"></i> <span class="ms-2">{{ $type->icon }}</span>@else<span class="text-muted">No icon</span>@endif</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><td class="fw-bold text-muted">Color:</td><td><div class="d-flex gap-2"><span class="rounded-circle" style="width: 30px; height: 30px; background-color: {{ $type->color }};"></span><span>{{ $type->color }}</span></div></td></tr>
                            <tr><td class="fw-bold text-muted">Order:</td><td>{{ $type->order }}</td></tr>
                            <tr><td class="fw-bold text-muted">Status:</td><td>@if($type->is_active)<span class="badge bg-success">Active</span>@else<span class="badge bg-danger">Inactive</span>@endif</td></tr>
                            <tr><td class="fw-bold text-muted">Created:</td><td>{{ \Carbon\Carbon::parse($type->created_at)->format('F d, Y h:i A') }}</td></tr>
                        70
                    </div>
                </div>
                @if($type->description)
                <div class="mt-3"><hr><h6 class="fw-bold text-muted"><i class="fas fa-align-left me-2"></i> Description</h6><p>{{ $type->description }}</p></div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .bg-gradient-primary { background: linear-gradient(135deg, #FE171B, #cc1215); }
    .rounded-4 { border-radius: 20px; }
    .rounded-3 { border-radius: 12px; }
    .table-borderless td { padding: 10px 0; }
    .btn-info, .btn-danger, .btn-secondary { transition: transform 0.2s; }
    .btn-info:hover, .btn-danger:hover, .btn-secondary:hover { transform: translateY(-2px); }
</style>
<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Testimonial;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $rating_filter = '';
    public $status_filter = '';
    public $perPage = 10;
    
    public function deleteTestimonial($id)
    {
        $testimonial = Testimonial::find($id);
        if ($testimonial && $testimonial->client_avatar && \Storage::disk('public')->exists($testimonial->client_avatar)) {
            \Storage::disk('public')->delete($testimonial->client_avatar);
        }
        $testimonial->delete();
        session()->flash('message', 'Testimonial deleted successfully!');
    }
    
    public function toggleStatus($id)
    {
        $testimonial = Testimonial::find($id);
        $testimonial->is_active = !$testimonial->is_active;
        $testimonial->save();
        session()->flash('message', 'Status updated!');
    }
    
    public function toggleFeatured($id)
    {
        $testimonial = Testimonial::find($id);
        $testimonial->is_featured = !$testimonial->is_featured;
        $testimonial->save();
        session()->flash('message', 'Featured status updated!');
    }
    
    public function getTestimonialsProperty()
    {
        $query = Testimonial::query();
        
        if ($this->search) {
            $query->where('client_name', 'like', '%'.$this->search.'%')
                  ->orWhere('content', 'like', '%'.$this->search.'%');
        }
        
        if ($this->rating_filter) {
            $query->where('rating', $this->rating_filter);
        }
        
        if ($this->status_filter !== '') {
            $query->where('is_active', $this->status_filter);
        }
        
        return $query->orderBy('order')->paginate($this->perPage);
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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Testimonials Management</h2>
            <p class="text-muted">Manage client feedback and reviews</p>
        </div>
        <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Add Testimonial
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row mb-3">
                <div class="col-md-4 mb-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by name...">
                    </div>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select rounded-3" wire:model.live="rating_filter">
                        <option value="">All Ratings</option>
                        <option value="5">★★★★★ (5)</option>
                        <option value="4">★★★★☆ (4)</option>
                        <option value="3">★★★☆☆ (3)</option>
                        <option value="2">★★☆☆☆ (2)</option>
                        <option value="1">★☆☆☆☆ (1)</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <select class="form-select rounded-3" wire:model.live="perPage">
                        <option value="10">10 per page</option>
                        <option value="25">25 per page</option>
                        <option value="50">50 per page</option>
                    </select>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-user"></i>  Name</th>
                            <th><i class="fas fa-star"></i> Rating</th>
                            <th><i class="fas fa-comment-dots"></i> Content</th>
                            <th><i class="fas fa-crown"></i> Featured</th>
                            <th><i class="fas fa-toggle-on"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->testimonials as $testimonial)
                        <tr>
                            <td>
                                @if($testimonial->client_avatar)
                                    <img src="{{ asset('storage/' . $testimonial->client_avatar) }}" width="40" height="40" class="rounded-circle">
                                @else
                                    <div class="bg-light rounded-circle d-inline-flex p-2">
                                        <i class="fas fa-user fa-lg text-muted"></i>
                                    </div>
                                @endif
                                <strong>{{ $testimonial->client_name }}</strong><br><small class="text-muted">{{ $testimonial->client_position }} @ {{ $testimonial->client_company }}</small>
                            </td>
                            <td>
                                @for($i=1; $i<=5; $i++)
                                    <i class="fas fa-star {{ $i <= $testimonial->rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                            </td>
                            <td>{{ Str::limit($testimonial->content, 60) }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleFeatured({{ $testimonial->id }})" {{ $testimonial->is_featured ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $testimonial->id }})" {{ $testimonial->is_active ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.testimonials.view', $testimonial->id) }}" class="btn btn-sm btn-success"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <button wire:click="deleteTestimonial({{ $testimonial->id }})" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No testimonials found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $this->testimonials->links() }}
        </div>
    </div>
</div>
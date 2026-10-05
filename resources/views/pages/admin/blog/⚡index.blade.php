<?php

use Livewire\Component;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithPagination;
    
    public $search = '';
    public $category_filter = '';
    public $status_filter = '';
    public $featured_filter = '';
    public $perPage = 10;
    
    // Delete confirmation
    public $confirmDelete = false;
    public $deleteId = null;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'category_filter' => ['except' => ''],
        'status_filter' => ['except' => ''],
        'featured_filter' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }
    
    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
    
    public function updatedFeaturedFilter()
    {
        $this->resetPage();
    }
    
    public function updatedPerPage()
    {
        $this->resetPage();
    }
    
    public function resetFilters()
    {
        $this->search = '';
        $this->category_filter = '';
        $this->status_filter = '';
        $this->featured_filter = '';
        $this->perPage = 10;
        $this->resetPage();
    }
    
    public function confirmDeletePost($id)
    {
        $this->confirmDelete = true;
        $this->deleteId = $id;
    }
    
    public function deletePost()
    {
        $post = BlogPost::find($this->deleteId);
        
        if ($post) {
            // Delete featured image
            if ($post->featured_image && Storage::disk('public')->exists($post->featured_image)) {
                Storage::disk('public')->delete($post->featured_image);
            }
            
            // Delete gallery images
            $gallery = json_decode($post->gallery, true) ?? [];
            foreach ($gallery as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
            
            $post->delete();
            session()->flash('message', 'Post deleted successfully!');
        }
        
        $this->confirmDelete = false;
        $this->deleteId = null;
    }
    
    public function toggleFeatured($id)
    {
        $post = BlogPost::find($id);
        $post->is_featured = !$post->is_featured;
        $post->save();
        session()->flash('message', 'Post featured status updated!');
    }
    
    public function toggleStatus($id)
    {
        $post = BlogPost::find($id);
        $post->is_published = !$post->is_published;
        $post->published_at = $post->is_published ? now() : null;
        $post->save();
        session()->flash('message', 'Post status updated!');
    }
    
    // Computed properties
    public function getCategoriesProperty()
    {
        return BlogCategory::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getPostsProperty()
    {
        $query = BlogPost::with('category'); // Removed 'author'
        
        // Search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                  ->orWhere('slug', 'like', '%'.$this->search.'%')
                  ->orWhere('excerpt', 'like', '%'.$this->search.'%');
            });
        }
        
        // Category filter
        if (!empty($this->category_filter)) {
            $query->where('category_id', $this->category_filter);
        }
        
        // Status filter (published/draft)
        if ($this->status_filter === 'published') {
            $query->where('is_published', true);
        } elseif ($this->status_filter === 'draft') {
            $query->where('is_published', false);
        }
        
        // Featured filter
        if ($this->featured_filter === 'featured') {
            $query->where('is_featured', true);
        } elseif ($this->featured_filter === 'not_featured') {
            $query->where('is_featured', false);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
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
            <h2 class="fw-bold mb-0">Blog Posts</h2>
            <p class="text-muted">Manage all your blog posts</p>
        </div>
        <a href="{{ route('admin.blog.create') }}" class="btn btn-primary rounded-3">
            <i class="fas fa-plus-circle me-2"></i> Create New Post
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters Row -->
            <div class="row g-3 mb-4">
                <!-- Search -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 rounded-3" placeholder="Search by title, slug or excerpt...">
                    </div>
                </div>
                
                <!-- Category Filter -->
                <div class="col-md-3">
                    <select class="form-select rounded-3" wire:model.live="category_filter">
                        <option value="">All Categories</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Status Filter -->
                <div class="col-md-2">
                    <select class="form-select rounded-3" wire:model.live="status_filter">
                        <option value="">All Status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                
                <!-- Featured Filter -->
                <div class="col-md-2">
                    <select class="form-select rounded-3" wire:model.live="featured_filter">
                        <option value="">All Posts</option>
                        <option value="featured">Featured Only</option>
                        <option value="not_featured">Not Featured</option>
                    </select>
                </div>
                
                <!-- Per Page -->
                <div class="col-md-1">
                    <select class="form-select rounded-3" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
            
            <!-- Reset Filters Button -->
            @if($search || $category_filter || $status_filter || $featured_filter)
                <div class="mb-3">
                    <button wire:click="resetFilters" class="btn btn-sm btn-secondary rounded-3">
                        <i class="fas fa-undo-alt me-1"></i> Reset Filters
                    </button>
                </div>
            @endif
            
            <!-- Posts Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4"><i class="fas fa-hashtag text-primary me-2"></i> ID</th>
                            <th class="py-3 px-4"><i class="fas fa-image text-primary me-2"></i> Image</th>
                            <th class="py-3 px-4"><i class="fas fa-heading text-primary me-2"></i> Title</th>
                            <th class="py-3 px-4"><i class="fas fa-tag text-primary me-2"></i> Category</th>
                            <th class="py-3 px-4"><i class="fas fa-eye text-primary me-2"></i> Views</th>
                            <th class="py-3 px-4"><i class="fas fa-star text-primary me-2"></i> Featured</th>
                            <th class="py-3 px-4"><i class="fas fa-toggle-on text-primary me-2"></i> Status</th>
                            <th class="py-3 px-4"><i class="fas fa-calendar text-primary me-2"></i> Date</th>
                            <th class="py-3 px-4 text-center"><i class="fas fa-cog text-primary me-2"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->posts as $post)
                        <tr>
                            <td class="px-4">{{ $post->id }}</td>
                            <td class="px-4">
                                @if($post->featured_image)
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" width="50" height="40" class="rounded-2" style="object-fit: cover;">
                                @else
                                    <i class="fas fa-image fa-2x text-muted"></i>
                                @endif
                            </td>
                            <td class="px-4">
                                <strong>{{ Str::limit($post->title, 40) }}</strong>
                                <br>
                                <small class="text-muted">{{ Str::limit($post->slug, 30) }}</small>
                            </td>
                            <td class="px-4">
                                <span class="badge" style="background-color: {{ $post->category->color ?? '#6c757d' }}; color: white;">
                                    {{ $post->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-4">
                                <i class="fas fa-eye me-1"></i> {{ number_format($post->views) }}
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleFeatured({{ $post->id }})" {{ $post->is_featured ? 'checked' : '' }}>
                                </div>
                            </td>
                            <td class="px-4">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" wire:change="toggleStatus({{ $post->id }})" {{ $post->is_published ? 'checked' : '' }}>
                                </div>
                                @if($post->is_published)
                                    <small class="text-success d-block">Published</small>
                                @else
                                    <small class="text-danger d-block">Draft</small>
                                @endif
                            </td>
                            <td class="px-4">
                                <small>{{ \Carbon\Carbon::parse($post->created_at)->format('M d, Y') }}</small>
                            </td>
                            <td class="px-4 text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.blog.show', $post->id) }}" class="btn btn-sm btn-warning rounded-3 me-2" title="Edit Post">
                                        <i class="fas fa-eye me-1"></i> 
                                    </a>
                                    <a href="{{ route('admin.blog.edit', $post->id) }}" class="btn btn-sm btn-info rounded-3 me-2" title="Edit Post">
                                        <i class="fas fa-edit me-1"></i> 
                                    </a>
                                    <button wire:click="confirmDeletePost({{ $post->id }})" class="btn btn-sm btn-danger rounded-3" title="Delete Post">
                                        <i class="fas fa-trash-alt me-1"></i> 
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-blog fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No posts found</h5>
                                    <a href="{{ route('admin.blog.create') }}" class="btn btn-primary rounded-3 mt-2">
                                        <i class="fas fa-plus-circle me-2"></i> Create Your First Post
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $this->posts->links() }}
            </div>
            
            <!-- Stats -->
            <div class="mt-3 text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Showing {{ $this->posts->firstItem() ?? 0 }} to {{ $this->posts->lastItem() ?? 0 }} of {{ $this->posts->total() }} posts
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    @if($confirmDelete)
    <div class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); position: fixed; top:0; left:0; width:100%; height:100%; z-index:1050">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header bg-danger text-white rounded-top-4">
                    <h5 class="modal-title">
                        <i class="fas fa-trash-alt me-2"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('confirmDelete', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i>
                        <h5>Are you sure?</h5>
                        <p class="text-muted">This action cannot be undone. This will permanently delete the post and all associated images.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-3" wire:click="$set('confirmDelete', false)">
                        <i class="fas fa-times me-2"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger rounded-3" wire:click="deletePost">
                        <i class="fas fa-trash-alt me-2"></i> Yes, Delete
                    </button>
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
    .btn-info {
        background: #0dcaf0;
        border: none;
        transition: transform 0.2s;
    }
    .btn-info:hover {
        background: #0bb5d8;
        transform: translateY(-1px);
    }
    .btn-danger {
        transition: transform 0.2s;
    }
    .btn-danger:hover {
        transform: translateY(-1px);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
    }
    table td {
        vertical-align: middle;
    }
    .btn-sm {
        padding: 0.4rem 0.8rem;
    }
    .form-check-input:checked {
        background-color: #FE171B;
        border-color: #FE171B;
    }
</style>
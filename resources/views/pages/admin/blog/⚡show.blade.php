<?php

use Livewire\Component;
use App\Models\BlogPost;

new class extends Component
{
    public $postId;
    public $post = null;
    
    public function mount($id)
    {
        $this->postId = $id;
        $this->loadPost();
    }
    
    public function loadPost()
    {
        $this->post = BlogPost::with('category')
            ->where('id', $this->postId)
            ->first();
        
        if (!$this->post) {
            abort(404, 'Post not found');
        }
        
        // Increment view count
        $this->post->increment('views');
        $this->post->refresh();
    }
    
    public function getGalleryImagesProperty()
    {
        return json_decode($this->post->gallery, true) ?? [];
    }
};

?>

<div>
    @if($post)
        <div class="container py-4">
            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary rounded-3">
                    <i class="fas fa-arrow-left me-2"></i> Back
                </a>
            </div>
            
            <!-- Post Header -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <!-- Category Badge -->
                    <div class="mb-3">
                        <span class="badge" style="background-color: {{ $post->category->color ?? '#6c757d' }}; color: white; padding: 8px 16px;">
                            <i class="fas fa-tag me-1"></i> {{ $post->category->name ?? 'Uncategorized' }}
                        </span>
                        
                        @if($post->is_featured)
                            <span class="badge bg-warning text-dark ms-2" style="padding: 8px 16px;">
                                <i class="fas fa-star me-1"></i> Featured
                            </span>
                        @endif
                        
                        @if($post->is_published)
                            <span class="badge bg-success ms-2" style="padding: 8px 16px;">
                                <i class="fas fa-check-circle me-1"></i> Published
                            </span>
                        @else
                            <span class="badge bg-danger ms-2" style="padding: 8px 16px;">
                                <i class="fas fa-draft me-1"></i> Draft
                            </span>
                        @endif
                    </div>
                    
                    <!-- Title -->
                    <h1 class="fw-bold mb-3">{{ $post->title }}</h1>
                    
                    <!-- Post Meta -->
                    <div class="d-flex flex-wrap gap-3 mb-4 text-muted">
                        <div>
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ \Carbon\Carbon::parse($post->created_at)->format('F d, Y') }}
                        </div>
                        <div>
                            <i class="fas fa-eye me-1"></i>
                            {{ number_format($post->views) }} views
                        </div>
                        @if($post->tags)
                            <div>
                                <i class="fas fa-tags me-1"></i>
                                {{ $post->tags }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Featured Image -->
                    @if($post->featured_image)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $post->featured_image) }}" 
                                 class="img-fluid rounded-3 w-100" 
                                 alt="{{ $post->title }}"
                                 style="max-height: 500px; object-fit: cover;">
                        </div>
                    @endif
                    
                    <!-- Excerpt -->
                    @if($post->excerpt)
                        <div class="alert alert-light rounded-3 mb-4">
                            <i class="fas fa-quote-left text-primary me-2"></i>
                            {{ $post->excerpt }}
                        </div>
                    @endif
                    
                    <!-- Content -->
                    <div class="post-content">
                        {!! $post->content !!}
                    </div>
                </div>
            </div>
            
            <!-- Gallery Section -->
            @if($this->galleryImages && count($this->galleryImages) > 0)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0">
                            <i class="fas fa-images me-2"></i> Gallery
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            @foreach($this->galleryImages as $image)
                                <div class="col-md-3 col-sm-4 col-6">
                                    <a href="{{ asset('storage/' . $image) }}" data-lightbox="gallery" data-title="{{ $post->title }}">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             class="img-fluid rounded-3" 
                                             style="height: 150px; width: 100%; object-fit: cover; cursor: pointer;"
                                             alt="Gallery image">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- SEO Meta Info -->
            @if($post->seo_title || $post->seo_description)
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-light rounded-top-4">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-line text-primary me-2"></i> SEO Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        @if($post->seo_title)
                            <div class="mb-3">
                                <strong class="text-primary">SEO Title:</strong>
                                <p class="mb-0">{{ $post->seo_title }}</p>
                            </div>
                        @endif
                        
                        @if($post->seo_description)
                            <div>
                                <strong class="text-primary">SEO Description:</strong>
                                <p class="mb-0">{{ $post->seo_description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            
            <!-- Action Buttons -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.blog.index') }}" class="btn btn-secondary rounded-3">
                    <i class="fas fa-list me-2"></i> All Posts
                </a>
                <div>
                    <a href="{{ route('admin.blog.edit', $post->id) }}" class="btn btn-info rounded-3 me-2">
                        <i class="fas fa-edit me-2"></i> Edit Post
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-blog fa-4x text-muted mb-3"></i>
            <h3>Post not found</h3>
            <a href="{{ route('admin.blog.index') }}" class="btn btn-primary rounded-3 mt-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Posts
            </a>
        </div>
    @endif
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .post-content {
        font-size: 16px;
        line-height: 1.8;
    }
    .post-content h1, .post-content h2, .post-content h3 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    .post-content p {
        margin-bottom: 1rem;
    }
    .post-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 1rem 0;
    }
    .post-content ul, .post-content ol {
        margin-bottom: 1rem;
        padding-left: 1.5rem;
    }
    .post-content li {
        margin-bottom: 0.5rem;
    }
    .post-content pre {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 12px;
        overflow-x: auto;
    }
    .post-content code {
        background: #f8f9fa;
        padding: 2px 6px;
        border-radius: 6px;
        font-size: 14px;
    }
    .btn-info {
        background: #0dcaf0;
        border: none;
        transition: transform 0.2s;
    }
    .btn-info:hover {
        background: #0bb5d8;
        transform: translateY(-2px);
    }
    .btn-danger {
        transition: transform 0.2s;
    }
    .btn-danger:hover {
        transform: translateY(-2px);
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
    }
</style>
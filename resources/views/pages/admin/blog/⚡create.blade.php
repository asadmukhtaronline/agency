<?php

use Livewire\Component;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    use WithFileUploads;
    
    // Form fields
    public $category_id = '';
    public $title = '';
    public $slug = '';
    public $excerpt = '';
    public $content = '';
    public $featured_image = null;
    public $gallery = [];
    public $tags = '';
    public $seo_title = '';
    public $seo_description = '';
    public $is_featured = false;
    public $is_published = false;
    public $published_at = '';
    
    public function updatedTitle($value)
    {
        $this->slug = Str::slug($value);
    }
    
    public function save()
    {
        $this->validate([
            'category_id' => 'required|exists:blog_categories,id',
            'title' => 'required|min:3|max:255',
            'slug' => 'required|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|max:500',
            'content' => 'required|min:10',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'tags' => 'nullable|max:500',
            'seo_title' => 'nullable|max:60',
            'seo_description' => 'nullable|max:160',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date'
        ]);
        
        // Handle featured image
        $featuredImagePath = null;
        if ($this->featured_image) {
            $featuredImagePath = $this->featured_image->store('blog/featured', 'public');
        }
        
        // Handle gallery images
        $galleryPaths = [];
        if ($this->gallery) {
            foreach ($this->gallery as $image) {
                $galleryPaths[] = $image->store('blog/gallery', 'public');
            }
        }
        
        // Fix: Handle published_at properly - convert empty string to null
        $publishedAt = null;
        if ($this->is_published) {
            if (!empty($this->published_at)) {
                $publishedAt = $this->published_at;
            } else {
                $publishedAt = now();
            }
        }
        
        // Create post
        BlogPost::create([
            'category_id' => $this->category_id,
            'author_id' => Auth::id(),
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $featuredImagePath,
            'gallery' => json_encode($galleryPaths),
            'tags' => $this->tags,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'is_featured' => $this->is_featured,
            'is_published' => $this->is_published,
            'published_at' => $publishedAt,  // Fixed: Now null instead of empty string
            'views' => 0
        ]);
        
        session()->flash('message', 'Post created successfully!');
        
        // Reset form
        $this->reset([
            'category_id', 'title', 'slug', 'excerpt', 'content', 
            'featured_image', 'gallery', 'tags', 'seo_title', 
            'seo_description', 'is_featured', 'is_published', 'published_at'
        ]);
        
        return redirect()->back();
    }
    
    public function getCategoriesProperty()
    {
        return BlogCategory::where('is_active', true)->orderBy('name')->get();
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
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0">
                <i class="fas fa-plus-circle me-2"></i> Create New Blog Post
            </h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <!-- Title -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-heading text-primary me-1"></i> Post Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control rounded-3 @error('title') is-invalid @enderror" 
                                   wire:model="title" placeholder="Enter post title">
                            @error('title') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <!-- Slug -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-link text-primary me-1"></i> Slug <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control rounded-3 @error('slug') is-invalid @enderror" 
                                   wire:model="slug" placeholder="enter-post-slug">
                            @error('slug') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">URL-friendly version of the title (auto-generated)</small>
                        </div>
                        
                        <!-- Excerpt -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-align-left text-primary me-1"></i> Excerpt <span class="text-muted">(Short Description)</span>
                            </label>
                            <textarea class="form-control rounded-3 @error('excerpt') is-invalid @enderror" 
                                      wire:model="excerpt" rows="3" placeholder="Brief summary of the post..."></textarea>
                            @error('excerpt') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">Maximum 500 characters</small>
                        </div>
                        
                        <!-- Content -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-file-alt text-primary me-1"></i> Content <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control rounded-3 @error('content') is-invalid @enderror" 
                                      wire:model="content" 
                                      rows="15" 
                                      placeholder="Write your post content here..."></textarea>
                            @error('content') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">Minimum 10 characters required</small>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="col-md-4">
                        <!-- Category Dropdown -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag text-primary me-1"></i> Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select rounded-3 @error('category_id') is-invalid @enderror" wire:model="category_id">
                                <option value="">-- Select Category --</option>
                                @foreach($this->categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <!-- Featured Image -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-image text-primary me-1"></i> Featured Image
                            </label>
                            <input type="file" class="form-control rounded-3 @error('featured_image') is-invalid @enderror" 
                                   wire:model="featured_image" accept="image/*">
                            @error('featured_image') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            @if($featured_image)
                                <div class="mt-2">
                                    <img src="{{ $featured_image->temporaryUrl() }}" class="img-fluid rounded-3" style="max-height: 150px;">
                                    <small class="text-muted d-block">Preview</small>
                                </div>
                            @endif
                            <small class="text-muted">Max size: 2MB (JPG, PNG, GIF, WEBP)</small>
                        </div>
                        
                        <!-- Gallery Images -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-images text-primary me-1"></i> Gallery Images
                            </label>
                            <input type="file" class="form-control rounded-3 @error('gallery.*') is-invalid @enderror" 
                                   wire:model="gallery" accept="image/*" multiple>
                            @error('gallery.*') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            @if($gallery)
                                <div class="row g-2 mt-2">
                                    @foreach($gallery as $index => $img)
                                        <div class="col-4">
                                            <img src="{{ $img->temporaryUrl() }}" class="img-fluid rounded-3" style="height: 80px; object-fit: cover;">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <small class="text-muted">Multiple images allowed (Max 2MB each)</small>
                        </div>
                        
                        <!-- Tags -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hashtag text-primary me-1"></i> Tags
                            </label>
                            <input type="text" class="form-control rounded-3 @error('tags') is-invalid @enderror" 
                                   wire:model="tags" placeholder="laravel, php, webdev">
                            @error('tags') 
                                <div class="text-danger small mt-1">
                                    <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            <small class="text-muted">Separate tags with commas</small>
                        </div>
                        
                        <!-- SEO Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-chart-line text-primary me-2"></i> SEO Settings
                                </h6>
                                
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">SEO Title</label>
                                    <input type="text" class="form-control form-control-sm rounded-3 @error('seo_title') is-invalid @enderror" 
                                           wire:model="seo_title" placeholder="SEO optimized title">
                                    @error('seo_title') 
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="text-muted">Max 60 characters</small>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">SEO Description</label>
                                    <textarea class="form-control form-control-sm rounded-3 @error('seo_description') is-invalid @enderror" 
                                              wire:model="seo_description" rows="2" placeholder="Meta description for SEO"></textarea>
                                    @error('seo_description') 
                                        <div class="text-danger small mt-1">
                                            <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="text-muted">Max 160 characters</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Featured & Publish -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_featured" wire:model="is_featured">
                                <label class="form-check-label fw-semibold" for="is_featured">
                                    <i class="fas fa-star text-warning me-1"></i> Featured Post
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_published" wire:model="is_published">
                                <label class="form-check-label fw-semibold" for="is_published">
                                    <i class="fas fa-check-circle text-success me-1"></i> Publish Now
                                </label>
                            </div>
                        </div>
                        
                        @if($is_published)
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">
                                    <i class="fas fa-calendar-alt me-1"></i> Publish Date
                                </label>
                                <input type="datetime-local" class="form-control rounded-3 @error('published_at') is-invalid @enderror" 
                                       wire:model="published_at">
                                @error('published_at') 
                                    <div class="text-danger small mt-1">
                                        <i class="fas fa-exclamation-circle me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                                <small class="text-muted">Leave empty to use current date/time</small>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                    <button type="reset" class="btn btn-secondary px-4 py-2 rounded-3" onclick="window.location.reload()">
                        <i class="fas fa-undo-alt me-2"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Publish Post
                    </button>
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .form-check-input:checked {
        background-color: #FE171B;
        border-color: #FE171B;
    }
    .text-danger {
        font-size: 12px;
        margin-top: 5px;
    }
    .is-invalid {
        border-color: #dc3545 !important;
    }
</style>
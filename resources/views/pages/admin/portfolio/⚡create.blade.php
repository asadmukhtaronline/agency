<?php

use Livewire\Component;
use App\Models\PortfolioItem;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    public $title = '';
    public $slug = '';
    public $category = '';
    public $description = '';
    public $client_name = '';
    public $project_url = '';
    public $completion_date = '';
    public $is_featured = false;
    public $is_active = true;
    public $order = 0;
    
    // Images
    public $featured_image = null;
    public $gallery = [];
    public $tempGallery = [];
    
    // Arrays
    public $technologies = [];
    public $challenges = [];
    public $solutions = [];
    public $results = [];
    
    // Input fields
    public $technologyInput = '';
    public $challengeInput = '';
    public $solutionInput = '';
    public $resultInput = '';
    
    public function updatedTitle($value)
    {
        $this->slug = Str::slug($value);
    }
    
    public function addTechnology()
    {
        if (!empty(trim($this->technologyInput))) {
            $this->technologies[] = trim($this->technologyInput);
            $this->technologyInput = '';
        }
    }
    
    public function removeTechnology($index)
    {
        unset($this->technologies[$index]);
        $this->technologies = array_values($this->technologies);
    }
    
    public function addChallenge()
    {
        if (!empty(trim($this->challengeInput))) {
            $this->challenges[] = trim($this->challengeInput);
            $this->challengeInput = '';
        }
    }
    
    public function removeChallenge($index)
    {
        unset($this->challenges[$index]);
        $this->challenges = array_values($this->challenges);
    }
    
    public function addSolution()
    {
        if (!empty(trim($this->solutionInput))) {
            $this->solutions[] = trim($this->solutionInput);
            $this->solutionInput = '';
        }
    }
    
    public function removeSolution($index)
    {
        unset($this->solutions[$index]);
        $this->solutions = array_values($this->solutions);
    }
    
    public function addResult()
    {
        if (!empty(trim($this->resultInput))) {
            $this->results[] = trim($this->resultInput);
            $this->resultInput = '';
        }
    }
    
    public function removeResult($index)
    {
        unset($this->results[$index]);
        $this->results = array_values($this->results);
    }
    
    public function removeGalleryImage($index)
    {
        unset($this->gallery[$index]);
        $this->gallery = array_values($this->gallery);
    }
    
    public function save()
    {
        $this->validate([
            'title' => 'required|min:3|max:255',
            'slug' => 'required|unique:portfolio_items,slug|max:255',
            'category' => 'required|max:100',
            'description' => 'required|min:50',
            'client_name' => 'nullable|max:255',
            'project_url' => 'nullable|url|max:255',
            'completion_date' => 'nullable|date',
            'featured_image' => 'nullable|image|max:2048',
            'gallery.*' => 'nullable|image|max:2048',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer'
        ]);
        
        // Upload featured image
        $imagePath = null;
        if ($this->featured_image) {
            $imagePath = $this->featured_image->store('portfolio', 'public');
        }
        
        // Upload gallery images
        $galleryPaths = [];
        if ($this->gallery) {
            foreach ($this->gallery as $galleryImage) {
                $galleryPaths[] = $galleryImage->store('portfolio/gallery', 'public');
            }
        }
        
        PortfolioItem::create([
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'description' => $this->description,
            'client_name' => $this->client_name,
            'project_url' => $this->project_url,
            'featured_image' => $imagePath,
            'gallery' => json_encode($galleryPaths),
            'technologies' => json_encode($this->technologies),
            'challenges' => json_encode($this->challenges),
            'solutions' => json_encode($this->solutions),
            'results' => json_encode($this->results),
            'completion_date' => $this->completion_date,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ]);
        
        session()->flash('message', 'Portfolio item created successfully!');
        return redirect()->route('admin.portfolio.index');
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
            <h2 class="fw-bold mb-0">Create Portfolio Item</h2>
            <p class="text-muted">Add a new project to showcase your work</p>
        </div>
        <a href="{{ route('admin.portfolio.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Portfolio
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i> Portfolio Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <!-- Basic Information -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Project Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('title') is-invalid @enderror" wire:model="title">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('slug') is-invalid @enderror" wire:model="slug">
                        @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted">Auto-generated from title</small>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 @error('category') is-invalid @enderror" wire:model="category" placeholder="e.g., Web Development, Mobile App">
                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Client Name</label>
                        <input type="text" class="form-control rounded-3" wire:model="client_name" placeholder="Client name">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Completion Date</label>
                        <input type="date" class="form-control rounded-3" wire:model="completion_date">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Project URL</label>
                    <input type="url" class="form-control rounded-3" wire:model="project_url" placeholder="https://example.com">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control rounded-3 @error('description') is-invalid @enderror" wire:model="description" rows="5"></textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                
                <!-- Images -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Featured Image</label>
                        <input type="file" class="form-control rounded-3" wire:model="featured_image" accept="image/*">
                        @error('featured_image') <span class="text-danger small">{{ $message }}</span> @enderror
                        @if($featured_image)
                            <div class="mt-2">
                                <img src="{{ $featured_image->temporaryUrl() }}" width="150" class="rounded-3 shadow-sm">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Gallery Images</label>
                        <input type="file" class="form-control rounded-3" wire:model="gallery" multiple accept="image/*">
                        @error('gallery.*') <span class="text-danger small">{{ $message }}</span> @enderror
                        @if($gallery)
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                @foreach($gallery as $index => $img)
                                    <div class="position-relative">
                                        <img src="{{ $img->temporaryUrl() }}" width="80" class="rounded-3 shadow-sm">
                                        <button type="button" wire:click="removeGalleryImage({{ $index }})" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle">×</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Technologies -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Technologies Used</label>
                    <div class="input-group mb-2">
                        <input type="text" wire:model="technologyInput" class="form-control rounded-3" placeholder="e.g., Laravel, React, Tailwind CSS">
                        <button type="button" wire:click="addTechnology" class="btn btn-primary rounded-3">Add Technology</button>
                    </div>
                    @if(count($technologies) > 0)
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach($technologies as $index => $tech)
                                <span class="badge bg-info text-dark p-2 rounded-3">
                                    <i class="fas fa-code me-1"></i> {{ $tech }}
                                    <button type="button" wire:click="removeTechnology({{ $index }})" class="btn-close ms-2 btn-sm"></button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                <!-- Challenges -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Challenges Faced</label>
                    <div class="input-group mb-2">
                        <input type="text" wire:model="challengeInput" class="form-control rounded-3" placeholder="Enter a challenge">
                        <button type="button" wire:click="addChallenge" class="btn btn-primary rounded-3">Add Challenge</button>
                    </div>
                    @if(count($challenges) > 0)
                        <div class="mt-2">
                            <ul class="list-group">
                                @foreach($challenges as $index => $challenge)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $challenge }}
                                        <button type="button" wire:click="removeChallenge({{ $index }})" class="btn btn-sm btn-danger rounded-3">Remove</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
                <!-- Solutions -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Solutions Provided</label>
                    <div class="input-group mb-2">
                        <input type="text" wire:model="solutionInput" class="form-control rounded-3" placeholder="Enter a solution">
                        <button type="button" wire:click="addSolution" class="btn btn-primary rounded-3">Add Solution</button>
                    </div>
                    @if(count($solutions) > 0)
                        <div class="mt-2">
                            <ul class="list-group">
                                @foreach($solutions as $index => $solution)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $solution }}
                                        <button type="button" wire:click="removeSolution({{ $index }})" class="btn btn-sm btn-danger rounded-3">Remove</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
                <!-- Results -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Results Achieved</label>
                    <div class="input-group mb-2">
                        <input type="text" wire:model="resultInput" class="form-control rounded-3" placeholder="Enter a result">
                        <button type="button" wire:click="addResult" class="btn btn-primary rounded-3">Add Result</button>
                    </div>
                    @if(count($results) > 0)
                        <div class="mt-2">
                            <ul class="list-group">
                                @foreach($results as $index => $result)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $result }}
                                        <button type="button" wire:click="removeResult({{ $index }})" class="btn btn-sm btn-danger rounded-3">Remove</button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                
                <!-- Order and Status -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Display Order</label>
                        <input type="number" class="form-control rounded-3" wire:model="order" placeholder="0">
                        <small class="text-muted">Lower numbers appear first</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isFeatured" wire:model="is_featured">
                            <label class="form-check-label fw-semibold" for="isFeatured">Feature this project</label>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input" id="isActive" wire:model="is_active">
                            <label class="form-check-label fw-semibold" for="isActive">Active (Visible on website)</label>
                        </div>
                    </div>
                </div>
                
                <!-- Submit -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Save Portfolio Item
                    </button>
                    <a href="{{ route('admin.portfolio.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
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
    .badge {
        font-size: 0.85rem;
        font-weight: 500;
    }
    .list-group-item {
        border-radius: 10px !important;
        margin-bottom: 5px;
    }
    .position-relative {
        position: relative;
    }
</style>
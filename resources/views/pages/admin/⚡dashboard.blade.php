<?php

use Livewire\Component;
use App\Models\Client;
use App\Models\Service;
use App\Models\Product;
use App\Models\PortfolioItem;
use App\Models\Testimonial;
use App\Models\Estimate;
use App\Models\Quotation;
use App\Models\BlogPost;
use App\Models\Country;

new class extends Component
{
    public $stats = [];
    public $recentEstimates = [];
    public $recentClients = [];
    public $recentQuotations = [];
    
    public function mount()
    {
        // Total counts
        $this->stats = [
            'total_clients' => Client::count(),
            'total_active_clients' => Client::where('is_active', true)->count(),
            'total_services' => Service::count(),
            'total_products' => Product::count(),
            'total_portfolio' => PortfolioItem::count(),
            'total_testimonials' => Testimonial::count(),
            'total_estimates' => Estimate::count(),
            'total_quotations' => Quotation::count(),
            'total_blogs' => BlogPost::count(),
            'total_countries' => Country::count(),
            'pending_estimates' => Estimate::where('status', 'draft')->count(),
            'accepted_estimates' => Estimate::where('status', 'accepted')->count(),
            'total_revenue' => Estimate::where('status', 'accepted')->sum('total'),
        ];
        
        // Recent data
        $this->recentClients = Client::latest()->take(5)->get();
        $this->recentEstimates = Estimate::with('client')->latest()->take(5)->get();
        $this->recentQuotations = Quotation::latest()->take(5)->get();
    }
};

?>

<div>
    <!-- Stats Cards Row -->
    <div class="row g-4 mb-4">
        <!-- Clients Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-users me-1"></i> Total Clients
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_clients']) }}</h2>
                            <small class="text-success mt-2 d-block">
                                <i class="fas fa-user-check me-1"></i> {{ $stats['total_active_clients'] }} Active
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Services Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-cogs me-1"></i> Services
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_services']) }}</h2>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-plus-circle me-1"></i> Available Services
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-cogs fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Products Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-box me-1"></i> Products
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_products']) }}</h2>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-tag me-1"></i> Available Products
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-box fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Portfolio Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-briefcase me-1"></i> Portfolio
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_portfolio']) }}</h2>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-check-circle me-1"></i> Completed Projects
                            </small>
                        </div>
                        <div class="bg-dark bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-briefcase fa-2x text-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Second Row -->
    <div class="row g-4 mb-4">
        <!-- Testimonials Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-star me-1"></i> Testimonials
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_testimonials']) }}</h2>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-comment me-1"></i> Client Feedback
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-star fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estimates Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-file-invoice me-1"></i> Estimates
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_estimates']) }}</h2>
                            <small class="text-warning mt-2 d-block">
                                <i class="fas fa-clock me-1"></i> {{ $stats['pending_estimates'] }} Pending
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-file-invoice fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quotations Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Quotations
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_quotations']) }}</h2>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-envelope me-1"></i> Received Requests
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-file-invoice-dollar fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Blogs Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-blog me-1"></i> Blog Posts
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_blogs']) }}</h2>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-newspaper me-1"></i> Published Articles
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-blog fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Revenue & Countries Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-white-50 text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-dollar-sign me-1"></i> Total Revenue
                            </p>
                            <h2 class="mb-0 fw-bold">${{ number_format($stats['total_revenue'], 2) }}</h2>
                            <small class="text-white-50 mt-2 d-block">
                                <i class="fas fa-chart-line me-1"></i> From Accepted Estimates
                            </small>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Accepted Estimates Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-check-circle me-1"></i> Accepted Estimates
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['accepted_estimates']) }}</h2>
                            <small class="text-success mt-2 d-block">
                                <i class="fas fa-thumbs-up me-1"></i> Converted to Projects
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Countries Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-globe me-1"></i> Countries
                            </p>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_countries']) }}</h2>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-flag me-1"></i> Global Presence
                            </small>
                        </div>
                        <div class="bg-secondary bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-globe fa-2x text-secondary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">
                                <i class="fas fa-bolt me-1"></i> Quick Actions
                            </p>
                            <div class="mt-2">
                                <a href="{{ route('admin.clients.create') }}" class="btn btn-sm btn-outline-primary me-1 mb-1">Add Client</a>
                                <a href="{{ route('admin.estimates.create') }}" class="btn btn-sm btn-outline-success me-1 mb-1">New Estimate</a>
                                <a href="{{ route('admin.services.create') }}" class="btn btn-sm btn-outline-info me-1 mb-1">Add Service</a>
                                <a href="{{ route('admin.portfolio.create') }}" class="btn btn-sm btn-outline-dark mb-1">Add Portfolio</a>
                            </div>
                        </div>
                        <div class="bg-light p-3 rounded-3">
                            <i class="fas fa-bolt fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Data Tables -->
    <div class="row g-4">
        <!-- Recent Clients -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-users text-primary me-2"></i> Recent Clients
                    </h5>
                </div>
                <div class="card-body p-4 pt-0">
                    @forelse($recentClients as $client)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="mb-0">{{ $client->name }}</h6>
                                <small class="text-muted">{{ $client->email }}</small>
                            </div>
                            <a href="{{ route('admin.clients.show', $client->id) }}" class="btn btn-sm btn-outline-primary">
                                View <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No clients yet</p>
                    @endforelse
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-link">View All Clients →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Estimates -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-file-invoice text-success me-2"></i> Recent Estimates
                    </h5>
                </div>
                <div class="card-body p-4 pt-0">
                    @forelse($recentEstimates as $estimate)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="mb-0">{{ $estimate->estimate_number }}</h6>
                                <small class="text-muted">{{ $estimate->client->name ?? 'N/A' }}</small>
                                <br>
                                <small class="text-muted">${{ number_format($estimate->total, 2) }}</small>
                            </div>
                            <span class="badge bg-{{ $estimate->status == 'draft' ? 'secondary' : ($estimate->status == 'accepted' ? 'success' : 'primary') }}">
                                {{ ucfirst($estimate->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No estimates yet</p>
                    @endforelse
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.estimates.index') }}" class="btn btn-sm btn-link">View All Estimates →</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Quotations -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-file-invoice-dollar text-info me-2"></i> Recent Quotations
                    </h5>
                </div>
                <div class="card-body p-4 pt-0">
                    @forelse($recentQuotations as $quotation)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="mb-0">{{ $quotation->name }}</h6>
                                <small class="text-muted">{{ $quotation->email }}</small>
                            </div>
                            <span class="badge bg-{{ $quotation->status == 'pending' ? 'warning' : ($quotation->status == 'accepted' ? 'success' : 'secondary') }}">
                                {{ ucfirst($quotation->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No quotations yet</p>
                    @endforelse
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.quotations.index') }}" class="btn btn-sm btn-link">View All Quotations →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats Footer -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-gradient-primary text-white">
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3 mb-md-0">
                            <h3 class="mb-0">{{ number_format($stats['total_clients']) }}</h3>
                            <small>Happy Clients</small>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <h3 class="mb-0">{{ number_format($stats['total_services'] + $stats['total_products']) }}</h3>
                            <small>Services & Products</small>
                        </div>
                        <div class="col-md-3 mb-3 mb-md-0">
                            <h3 class="mb-0">{{ number_format($stats['total_estimates']) }}</h3>
                            <small>Estimates Generated</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="mb-0">${{ number_format($stats['total_revenue'], 0) }}</h3>
                            <small>Total Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    .bg-opacity-10 {
        opacity: 0.9;
    }
    .bg-opacity-20 {
        opacity: 0.8;
    }
</style>
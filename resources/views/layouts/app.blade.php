<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Panel - WebInventorz' }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SimpleMDE - Lightweight Markdown Editor -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
<script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f5f7fa;
            overflow-x: hidden;
            height: 100vh;
        }
        
        /* ========== SIDEBAR STYLES ========== */
        
        .sidebar {
            background: #ffffff;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            border-right: 1px solid #eef2f6;
            display: flex;
            flex-direction: column;
        }
        
        /* Sidebar Scroll Area */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 20px;
        }
        
        /* Custom Scrollbar for Sidebar */
        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar-nav::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: #FE171B;
        }
        
        /* Logo Section - Fixed at Top */
        .sidebar .logo-section {
            padding: 20px;
            border-bottom: 1px solid #eef2f6;
            flex-shrink: 0;
        }
        
        .sidebar .logo-section img {
            max-height: 40px;
        }
        
        /* Navigation Links */
        .sidebar .nav-link {
            color: #475569;
            padding: 10px 16px;
            margin: 2px 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
            position: relative;
        }
        
        .sidebar .nav-link:hover {
            background: #fef2f2;
            color: #FE171B;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #FE171B, #cc1215);
            color: white;
            box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
        }
        
        .sidebar .nav-link i {
            width: 24px;
            margin-right: 10px;
            font-size: 16px;
        }
        
        /* Dropdown Toggle Arrow */
        .sidebar .dropdown-toggle::after {
            float: right;
            margin-top: 8px;
            border-top-color: #475569;
        }
        
        .sidebar .nav-link.active.dropdown-toggle::after {
            border-top-color: white;
        }
        
        /* Dropdown Menu - Inside Sidebar */
        .sidebar .dropdown-menu {
            position: relative !important;
            width: calc(100% - 20px);
            margin: 0 10px 8px 10px;
            padding: 5px 0;
            background: #f8fafc;
            border: none;
            border-radius: 10px;
            box-shadow: none;
            transform: none !important;
            top: 0 !important;
        }
        
        .sidebar .dropdown-item {
            color: #475569;
            padding: 8px 16px 8px 45px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 6px;
        }
        
        .sidebar .dropdown-item:hover {
            background: #fef2f2;
            color: #FE171B;
            padding-left: 50px;
        }
        
        .sidebar .dropdown-item i {
            width: 20px;
            margin-right: 8px;
            color: #94a3b8;
        }
        
        .sidebar .dropdown-item:hover i {
            color: #FE171B;
        }
        
        .sidebar .dropdown-divider {
            margin: 6px 12px;
            border-color: #e2e8f0;
        }
        
        /* Divider */
        .sidebar hr {
            margin: 10px 16px;
            border-color: #eef2f6;
        }
        
        /* ========== MAIN CONTENT STYLES ========== */
        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
        }
        
        /* Top Navbar */
        .navbar-top {
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            border: 1px solid #eef2f6;
        }
        
        .navbar-top .welcome-text h5 {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }
        
        .navbar-top .welcome-text p {
            font-size: 12px;
            color: #64748b;
            margin: 0;
        }
        
        /* User Dropdown */
        .user-dropdown .dropdown-toggle {
            background: #f8fafc;
            border: 1px solid #eef2f6;
            border-radius: 40px;
            padding: 6px 14px;
            font-weight: 500;
            color: #1e293b;
            font-size: 13px;
        }
        
        .user-dropdown .dropdown-toggle:hover {
            background: #fef2f2;
            border-color: #FE171B;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .navbar-top {
                padding: 10px 15px;
            }
        }
        
        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .main-content {
            animation: fadeIn 0.4s ease;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <!-- Logo Section - Fixed -->
    <div class="logo-section">
        <img src="{{ asset('logo.png') }}" alt="Logo" height="40" 
             onerror="this.src='https://placehold.co/150x50/FE171B/white?text=W'">
    </div>
    
    <!-- Scrollable Navigation Menu -->
    <div class="sidebar-nav">
        <nav class="nav flex-column">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-address-book"></i> CRM
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.crm.dashboard') }}" class="dropdown-item"><i class="fas fa-tachometer-alt"></i> Dashboard </a></li>
                    <li><a href="{{ route('admin.crm.leads.create') }}" class="dropdown-item"><i class="fas fa-users"></i> Create Leads </a></li>
                    <li><a href="{{ route('admin.crm.leads.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Leads </a></li>
                    <li><a href="{{ route('admin.crm.communication.start') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Start Communication</a></li>
                    <li><a href="{{ route('admin.crm.communication') }}" class="dropdown-item"><i class="fas fa-share-alt"></i> Communication</a></li>
                    <li><a href="{{ route('admin.crm.worktype') }}" class="dropdown-item"><i class="fas fa-briefcase"></i> Work Type </a></li>
                    <li><a href="{{ route('admin.crm.type') }}" class="dropdown-item"><i class="fas fa-list"></i> Client Type</a></li>
                </ul>
            </div> 
                        <!-- Project Management Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-project-diagram"></i> Project Management
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.projects.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Projects</a></li>
                    <li><a href="{{ route('admin.projects.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> New Project</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="{{ route('admin.milestones.index') }}" class="dropdown-item"><i class="fas fa-flag-checkered"></i> Milestones</a></li>
                    <li><a href="{{ route('admin.milestones.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Add Milestone</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="{{ route('admin.payments.index') }}" class="dropdown-item"><i class="fas fa-money-bill-wave"></i> Payments</a></li>
                    <li><a href="{{ route('admin.payments.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Record Payment</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="{{ route('admin.payment-gateways.index') }}" class="dropdown-item"><i class="fas fa-credit-card"></i> Payment Gateways</a></li>
                </ul>
            </div>
                        
            <!-- Clients Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-users"></i> Clients
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.clients.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Clients</a></li>
                    <li><a href="{{ route('admin.clients.create') }}" class="dropdown-item"><i class="fas fa-user-plus"></i> Add New Client</a></li>
                </ul>
            </div>
            <!-- Services Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-cogs"></i> Services
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.services.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Services</a></li>
                    <li><a href="{{ route('admin.services.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Add New Service</a></li>
                </ul>
            </div>
            
            <!-- Products Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-box"></i> Products
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.products.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Products</a></li>
                    <li><a href="{{ route('admin.products.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Add New Product</a></li>
                    <li><a href="{{ route('admin.product-categories.index') }}" class="dropdown-item"><i class="fas fa-folder"></i> Categories</a></li>
                </ul>
            </div>
            
            <!-- Portfolio Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-briefcase"></i> Portfolio
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.portfolio.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Projects</a></li>
                    <li><a href="{{ route('admin.portfolio.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Add New Project</a></li>
                </ul>
            </div>
             <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-users"></i> Members
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.designation') }}" class="dropdown-item"><i class="fas fa-list"></i> Designations </a></li>
                    <li><a href="{{ route('admin.team.members') }}" class="dropdown-item"><i class="fas fa-users"></i> All Members </a></li>
                    <li><a href="{{ route('admin.team.members.create') }}" class="dropdown-item"><i class="fas fa-user-plus"></i> Create New Member</a></li>
                </ul>
            </div>
            <!-- Testimonials Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-star"></i> Testimonials
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.testimonials.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Testimonials</a></li>
                    <li><a href="{{ route('admin.testimonials.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Add Testimonial</a></li>
                </ul>
            </div>
            
            <!-- Estimates Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-invoice-dollar"></i> Estimates
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.estimates.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Estimates</a></li>
                    <li><a href="{{ route('admin.estimates.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Create Estimate</a></li>
                </ul>
            </div>
            
            <!-- Quotations -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-file-invoice-dollar"></i> Quotations
                </a>
               <ul class="dropdown-menu">
    <li>
        <a href="{{ route('admin.quotations.index') }}" class="dropdown-item d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-file-invoice me-2"></i> Quotations
            </span>
            @php
                $pendingQuotations = App\Models\Quotation::where('status', 'pending')->count();
            @endphp
            @if($pendingQuotations > 0)
                <span class="badge bg-warning rounded-pill">{{ $pendingQuotations }}</span>
            @endif
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
        <a href="{{ route('admin.quotations.pricing') }}" class="dropdown-item d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-tags me-2"></i> Pricing Quote
            </span>
            @php
                $pendingPricingQuotes = App\Models\QuoteRequest::where('status', 'pending')->count();
            @endphp
            @if($pendingPricingQuotes > 0)
                <span class="badge bg-warning rounded-pill">{{ $pendingPricingQuotes }}</span>
            @endif
        </a>
    </li>
    <li><hr class="dropdown-divider"></li>
    <li>
        <a href="{{ route('admin.quotations.contact') }}" class="dropdown-item d-flex justify-content-between align-items-center">
            <span>
                <i class="fas fa-envelope-open-text me-2"></i> Contact Requests
            </span>
            @php
                $pendingContacts = App\Models\ContactInquiry::where('status', 'pending')->count();
            @endphp
            @if($pendingContacts > 0)
                <span class="badge bg-warning rounded-pill">{{ $pendingContacts }}</span>
            @endif
        </a>
    </li>
</ul>
            </div>
             <!-- Blog Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-money-bill"></i> Pricing
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.pricing.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Pricing </a></li>
                    <li><a href="{{ route('admin.pricing.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Create New Pricing</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="{{ route('admin.countries.pricing') }}" class="dropdown-item"><i class="fas fa-globe"></i> Countries</a></li>
                    <li><a href="{{ route('admin.type.pricing') }}" class="dropdown-item"><i class="fas fa-tag"></i> Type </a></li>
                </ul>
            </div>
            <!-- Blog Dropdown -->
            <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-blog"></i> Blog
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.blog.index') }}" class="dropdown-item"><i class="fas fa-list"></i> All Articles</a></li>
                    <li><a href="{{ route('admin.blog.create') }}" class="dropdown-item"><i class="fas fa-plus-circle"></i> Write New Article</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a href="{{ route('admin.blog.categories') }}" class="dropdown-item"><i class="fas fa-folder"></i> Categories</a></li>
                </ul>
            </div>
            @if(auth()->user()->isSuperAdmin())
             <div class="nav-item w-100">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-users"></i> User Management
                </a>
                <ul class="dropdown-menu">
                    <li><a href="{{ route('admin.team.users.index') }}" class="dropdown-item"><i class="fas fa-users"></i> All Users </a></li>
                    <li><a href="{{ route('admin.team.users.create') }}" class="dropdown-item"><i class="fas fa-user-plus"></i> New User </a></li>
                </ul>
            </div>
            @endif
            <hr>
            <a href="{{ route('admin.general') }}" class="nav-link">
                <i class="fas fa-globe"></i> General
            </a>
            <!-- Logout -->
            <a href="{{ route('logout') }}" class="nav-link" >
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
            
    </div>
</div>

<div class="main-content">
    <!-- Top Navbar -->
    <div class="navbar-top d-flex justify-content-between align-items-center">
        <div class="welcome-text">
            <button class="btn btn-light d-md-none me-3" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <h5>Welcome back, Asad Mukhtar!</h5>
                <p><i class="fas fa-calendar-alt me-1"></i> {{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            
            <!-- User Dropdown -->
            <div class="dropdown user-dropdown">
                <button class="btn dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle fa-2x text-primary"></i>
                    <span>{{ auth()->user()->name ?? 'Admin' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('admin.user.profile') }}"><i class="fas fa-user me-2"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.user.setting') }}"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Page Content -->
    {{ $slot }}
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@livewireScripts

<script>
    // Sidebar Toggle for Mobile
    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(event.target) && !toggleBtn?.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Active link highlight
    document.addEventListener('DOMContentLoaded', function() {
        const currentUrl = window.location.pathname;
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href && href !== '#' && currentUrl === href) {
                link.classList.add('active');
            }
        });
    });
</script>
</body>
</html>
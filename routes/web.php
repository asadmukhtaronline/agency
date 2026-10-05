<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Admin\Dashboard;
use App\Http\Controllers\loginController;
use Illuminate\Support\Facades\Auth;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});
Route::get('/logout',function(){
    Auth::logout();
    return redirect(route('login'));
})->name('logout');
// ========== ADMIN ROUTES ==========
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::livewire('/dashboard', 'pages::admin.dashboard')->name('dashboard');
    
    // Services
    Route::livewire('/services', 'pages::admin.services.index')->name('services.index');
    Route::livewire('/services/create', 'pages::admin.services.create')->name('services.create');
    Route::livewire('/services/{id}/edit', 'pages::admin.services.edit')->name('services.edit');
    
    // Products
    Route::livewire('/products', 'pages::admin.products.index')->name('products.index');
    Route::livewire('/products/create', 'pages::admin.products.create')->name('products.create');
    Route::livewire('/products/{id}/edit', 'pages::admin.products.edit')->name('products.edit');
    Route::livewire('/product-categories', 'pages::admin.products.categories')->name('product-categories.index');
    Route::livewire('/product/show/{id}', 'pages::admin.products.show')->name('product-categories.show');
    // Pricing
    Route::livewire('/pricing', 'pages::admin.pricing.index')->name('pricing.index');
    Route::livewire('/pricing/create', 'pages::admin.pricing.create')->name('pricing.create');
    Route::livewire('/pricing/edit/{id}', 'pages::admin.pricing.edit')->name('pricing.edit');
    Route::livewire('/pricing/countries', 'pages::admin.pricing.countries')->name('countries.pricing');
    Route::livewire('/pricing/type', 'pages::admin.pricing.type')->name('type.pricing');
    // Blog
    Route::livewire('/blog', 'pages::admin.blog.index')->name('blog.index');
    Route::livewire('/blog/create', 'pages::admin.blog.create')->name('blog.create');
    Route::livewire('/blog/edit/{id}', 'pages::admin.blog.edit')->name('blog.edit');
    Route::livewire('/blog/show/{id}', 'pages::admin.blog.show')->name('blog.show');
    Route::livewire('/blog/categories', 'pages::admin.blog.categories')->name('blog.categories');
    
    // Quotations
    Route::livewire('/quotations', 'pages::admin.quotations.index')->name('quotations.index');
    Route::livewire('/contact', 'pages::admin.qoutations.contact')->name('quotations.contact');
    Route::livewire('/quotations/pricing', 'pages::admin.qoutations.pqoute')->name('quotations.pricing');

    
    // Testimonials
    Route::livewire('/testimonials', 'pages::admin.testimonials.index')->name('testimonials.index');
    Route::livewire('/testimonials/create', 'pages::admin.testimonials.create')->name('testimonials.create');
    Route::livewire('/testimonials/{id}/view', 'pages::admin.testimonials.view')->name('testimonials.view');
    Route::livewire('/testimonials/edit/{id}', 'pages::admin.testimonials.edit')->name('testimonials.edit');

    
    // Portfolio
    Route::livewire('/portfolio', 'pages::admin.portfolio.index')->name('portfolio.index');
    Route::livewire('/portfolio/edit/{id}', 'pages::admin.portfolio.edit')->name('portfolio.edit');
    Route::livewire('/portfolio/view/{id}', 'pages::admin.portfolio.view')->name('portfolio.view');
    Route::livewire('/portfolio/create', 'pages::admin.portfolio.create')->name('portfolio.create');
    
    // client side ..
    // Clients Routes
    Route::livewire('/clients', 'pages::admin.clients.index')->name('clients.index');
    Route::livewire('/clients/create', 'pages::admin.clients.create')->name('clients.create');
    Route::livewire('/clients/{id}/edit', 'pages::admin.clients.edit')->name('clients.edit');
    Route::livewire('/clients/{id}', 'pages::admin.clients.show')->name('clients.show');
    // Estimates Routes
    Route::livewire('/estimates', 'pages::admin.estimates.index')->name('estimates.index');
    Route::livewire('/estimates/create', 'pages::admin.estimates.create')->name('estimates.create');
    Route::livewire('/estimates/{id}/edit', 'pages::admin.estimates.edit')->name('estimates.edit');
    Route::livewire('/estimates/{id}', 'pages::admin.estimates.show')->name('estimates.show');
    // payment gateway ..
    Route::livewire('/payment-gateways', 'pages::admin.payment-gateways.index')->name('payment-gateways.index');
    Route::livewire('/payment-gateways/create', 'pages::admin.payment-gateways.create')->name('payment-gateways.create');
    Route::livewire('/payment-gateways/{id}/edit', 'pages::admin.payment-gateways.edit')->name('payment-gateways.edit');
        // Projects
    Route::livewire('/projects', 'pages::admin.projects.index')->name('projects.index');
    Route::livewire('/projects/create', 'pages::admin.projects.create')->name('projects.create');
    Route::livewire('/projects/{id}/edit', 'pages::admin.projects.edit')->name('projects.edit');
    Route::livewire('/projects/{id}', 'pages::admin.projects.show')->name('projects.show');
    
    // Milestones
    Route::livewire('/milestones', 'pages::admin.milestones.index')->name('milestones.index');
    Route::livewire('/milestones/create', 'pages::admin.milestones.create')->name('milestones.create');
    Route::livewire('/milestones/{id}/edit', 'pages::admin.milestones.edit')->name('milestones.edit');
    
    // Payments
    Route::livewire('/payments', 'pages::admin.payments.index')->name('payments.index');
    Route::livewire('/payments/create', 'pages::admin.payments.create')->name('payments.create');
    Route::livewire('/payments/{id}', 'pages::admin.payments.show')->name('payments.show');
    // CRM ..
    Route::livewire('/crm/dashboard', 'pages::admin.crm.dashboard')->name('crm.dashboard');
    Route::livewire('/crm/type', 'pages::admin.crm.type')->name('crm.type');
    Route::livewire('/crm/type/show/{id}', 'pages::admin.crm.showtype')->name('crm.type.show');
    Route::livewire('/crm/lead/create', 'pages::admin.crm.leads.create')->name('crm.leads.create');
    Route::livewire('/crm/leads', 'pages::admin.crm.leads.index')->name('crm.leads.index');
    Route::livewire('/crm/leads/edit/{id}', 'pages::admin.crm.leads.edit')->name('crm.leads.edit');
    Route::livewire('/crm/show/{id}', 'pages::admin.crm.leads.show')->name('crm.leads.show');
    Route::livewire('/crm/type/work', 'pages::admin.crm.worktype.index')->name('crm.worktype');
    Route::livewire('/crm/type/work/{id}', 'pages::admin.crm.worktype.show')->name('crm.worktype.show');
    Route::livewire('/crm/leads/communication','pages::admin.crm.test')->name('crm.communication');
    Route::livewire('/crm/leads/start-communication','pages::admin.crm.start')->name('crm.communication.start');
    // Members ..
    Route::livewire('/crm/members/designation','pages::admin.members.desgination')->name('designation');
    Route::livewire('/crm/members','pages::admin.members.teammember')->name('team.members');
    Route::livewire('/crm/members/create','pages::admin.members.create')->name('team.members.create');
    Route::livewire('/crm/members/show/{id}','pages::admin.members.show')->name('team.members.show');
    Route::livewire('/crm/members/edit/{id}','pages::admin.members.edit')->name('team.members.edit');
    // user management ..
    Route::prefix('users')->group(function () {
        Route::livewire('/','pages::admin.users.index')->name('team.users.index');
        Route::livewire('/create','pages::admin.users.create')->name('team.users.create');
        Route::livewire('/edit/{id}','pages::admin.users.edit')->name('team.users.edit');
    });
    Route::livewire('/settings','pages::settings.setting')->name('user.setting');
    Route::livewire('/profile','pages::settings.profile')->name('user.profile');
    Route::livewire('/general','pages::settings.general')->name('general');
});
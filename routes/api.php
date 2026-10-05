<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\TeamApiController;
use App\Http\Controllers\Api\BlogApiController;
use App\Http\Controllers\Api\ServiceApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\PortfolioApiController;
use App\Http\Controllers\Api\TestimonialApiController;
use App\Http\Controllers\Api\CountryApiController;
use App\Http\Controllers\Api\PricingApiController;
use App\Http\Controllers\Api\QuotationApiController;
use App\Http\Controllers\Api\SettingsApiController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\ContactController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Country API Routes
Route::prefix('countries')->group(function () {
    Route::get('/', [CountryApiController::class, 'getCountries']);
    Route::get('/{identifier}', [CountryApiController::class, 'getCountry']);
});

// Pricing API Routes
Route::prefix('pricing')->group(function () {
    // Main Pricing
    Route::get('/', [PricingApiController::class, 'getPricingPlans']);
    Route::get('/{identifier}', [PricingApiController::class, 'getPricingPlan']);
    
    // By Country
    Route::get('/country/{countryCode}/plans', [PricingApiController::class, 'getPricingByCountry']);
    
    // By Service Type
    Route::get('/service-type/{serviceTypeId}/plans', [PricingApiController::class, 'getPricingByServiceType']);
    
    // Popular Plans
    Route::get('/popular/list', [PricingApiController::class, 'getPopularPlans']);
});

Route::prefix('team')->group(function () {
    // Designations
    Route::get('/designations', [TeamApiController::class, 'getDesignations']);
    Route::get('/designations/{id}', [TeamApiController::class, 'getDesignationById']);
    
    // Team Members
    Route::get('/members', [TeamApiController::class, 'getTeamMembers']);
    Route::get('/members/{id}', [TeamApiController::class, 'getTeamMemberById']);
    Route::get('/members/designation/{designationId}', [TeamApiController::class, 'getMembersByDesignation']);
});
// Blog API Routes
Route::prefix('blog')->group(function () {
    // Categories
    Route::get('/categories', [BlogApiController::class, 'getCategories']);
    Route::get('/categories/{identifier}', [BlogApiController::class, 'getCategory']);
    
    // Posts
    Route::get('/posts', [BlogApiController::class, 'getPosts']);
    Route::get('/posts/{identifier}', [BlogApiController::class, 'getPost']);
    Route::get('/posts/{id}/related', [BlogApiController::class, 'getRelatedPosts']);
    
    // Posts by category
    Route::get('/category/{categoryId}/posts', [BlogApiController::class, 'getPostsByCategory']);
    
    // Featured, Popular, Recent
    Route::get('/posts/featured', [BlogApiController::class, 'getFeaturedPosts']);
    Route::get('/posts/popular', [BlogApiController::class, 'getPopularPosts']);
    Route::get('/posts/recent', [BlogApiController::class, 'getRecentPosts']);
    
    // Tags
    Route::get('/tags', [BlogApiController::class, 'getTags']);
    Route::get('/tags/{tag}/posts', [BlogApiController::class, 'getPostsByTag']);
});
// Service API Routes
// Service API Routes
Route::prefix('services')->group(function () {
    // Main Services
    Route::get('/', [ServiceApiController::class, 'getServices']);
    Route::get('/{identifier}', [ServiceApiController::class, 'getService']);
    Route::get('/featured/list', [ServiceApiController::class, 'getFeaturedServices']);
    
    // Tags
    Route::get('/tags/all', [ServiceApiController::class, 'getTags']);
    Route::get('/tags/{tag}/services', [ServiceApiController::class, 'getServicesByTag']);
    
    // Service Types
    Route::get('/types/list', [ServiceApiController::class, 'getServiceTypes']);
    Route::get('/types/{identifier}', [ServiceApiController::class, 'getServiceType']);
});
// Product API Routes
Route::prefix('products')->group(function () {
    // Products
    Route::get('/', [ProductApiController::class, 'getProducts']);
    Route::get('/{identifier}', [ProductApiController::class, 'getProduct']);
    
    // Categories
    Route::get('/categories/all', [ProductApiController::class, 'getCategories']);
    Route::get('/categories/{identifier}', [ProductApiController::class, 'getCategory']);
    
    // Products by category slug (shortcut)
    Route::get('/category/{slug}/products', [ProductApiController::class, 'getProductsByCategorySlug']);
});
// Portfolio API Routes
Route::prefix('portfolio')->group(function () {
    Route::get('/', [PortfolioApiController::class, 'getPortfolio']);
    Route::get('/{identifier}', [PortfolioApiController::class, 'getPortfolioItem']);
});
// Testimonial API Routes
Route::prefix('testimonials')->group(function () {
    // Main Testimonials
    Route::get('/', [TestimonialApiController::class, 'getTestimonials']);
    Route::get('/{id}', [TestimonialApiController::class, 'getTestimonial']);
    
    // Featured
    Route::get('/featured/list', [TestimonialApiController::class, 'getFeaturedTestimonials']);
    
    // By Rating
    Route::get('/rating/{rating}/list', [TestimonialApiController::class, 'getTestimonialsByRating']);
    
    // Services
    Route::get('/services/all', [TestimonialApiController::class, 'getServices']);
});
// Public route - no auth required
Route::post('/quotations', [QuotationApiController::class, 'store']);
// Settings API Routes
Route::prefix('settings')->group(function () {
    // All settings
    Route::get('/', [SettingsApiController::class, 'getAllSettings']);
    Route::get('/{key}', [SettingsApiController::class, 'getSetting']);
    
    // Grouped settings
    Route::get('/group/{group}', [SettingsApiController::class, 'getSettingsByGroup']);
    Route::get('/general/all', [SettingsApiController::class, 'getGeneralSettings']);
    Route::get('/contact/all', [SettingsApiController::class, 'getContactSettings']);
    Route::get('/social/all', [SettingsApiController::class, 'getSocialSettings']);
    Route::get('/seo/all', [SettingsApiController::class, 'getSeoSettings']);
    Route::get('/business/all', [SettingsApiController::class, 'getBusinessHours']);
    Route::get('/footer/all', [SettingsApiController::class, 'getFooterSettings']);
});
// routes/api.php
Route::post('/quote-requests', [QuoteController::class, 'store']);
Route::post('/contact', [ContactController::class, 'store']);
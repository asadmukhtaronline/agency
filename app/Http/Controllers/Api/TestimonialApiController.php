<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\TestimonialApiController;


class TestimonialApiController extends Controller
{
    /**
     * Get all testimonials
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTestimonials(Request $request): JsonResponse
    {
        $query = Testimonial::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc');
        
        // Filter by featured
        if ($request->has('featured') && $request->featured == true) {
            $query->where('is_featured', true);
        }
        
        // Filter by rating (min rating)
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }
        
        // Filter by service used
        if ($request->has('service')) {
            $query->where('service_used', 'like', '%' . $request->service . '%');
        }
        
        // Search by client name or company
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('client_name', 'like', '%' . $search . '%')
                  ->orWhere('client_company', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $testimonials = $query->paginate($perPage);
        
        // Format response
        $testimonials->getCollection()->transform(function($testimonial) {
            return $this->formatTestimonialResponse($testimonial);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonials retrieved successfully',
            'data' => $testimonials->items(),
            'meta' => [
                'current_page' => $testimonials->currentPage(),
                'per_page' => $testimonials->perPage(),
                'total' => $testimonials->total(),
                'last_page' => $testimonials->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Get single testimonial by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function getTestimonial($id): JsonResponse
    {
        $testimonial = Testimonial::where('is_active', true)
            ->find($id);
        
        if (!$testimonial) {
            return response()->json([
                'success' => false,
                'message' => 'Testimonial not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Testimonial retrieved successfully',
            'data' => $this->formatTestimonialResponse($testimonial)
        ], 200);
    }
    
    /**
     * Get featured testimonials
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFeaturedTestimonials(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);
        
        $testimonials = Testimonial::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('order', 'asc')
            ->limit($limit)
            ->get();
        
        $testimonials = $testimonials->map(function($testimonial) {
            return $this->formatTestimonialResponse($testimonial);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Featured testimonials retrieved successfully',
            'data' => $testimonials,
            'total' => $testimonials->count()
        ], 200);
    }
    
    /**
     * Get testimonials by rating
     *
     * @param int $rating
     * @param Request $request
     * @return JsonResponse
     */
    public function getTestimonialsByRating($rating, Request $request): JsonResponse
    {
        $query = Testimonial::where('is_active', true)
            ->where('rating', $rating)
            ->orderBy('order', 'asc');
        
        $perPage = $request->get('per_page', 15);
        $testimonials = $query->paginate($perPage);
        
        $testimonials->getCollection()->transform(function($testimonial) {
            return $this->formatTestimonialResponse($testimonial);
        });
        
        return response()->json([
            'success' => true,
            'message' => "Testimonials with {$rating} stars retrieved successfully",
            'data' => $testimonials->items(),
            'meta' => [
                'current_page' => $testimonials->currentPage(),
                'per_page' => $testimonials->perPage(),
                'total' => $testimonials->total(),
                'last_page' => $testimonials->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Get all unique services used
     *
     * @return JsonResponse
     */
    public function getServices(): JsonResponse
    {
        $services = Testimonial::where('is_active', true)
            ->whereNotNull('service_used')
            ->distinct()
            ->pluck('service_used');
        
        return response()->json([
            'success' => true,
            'message' => 'Services retrieved successfully',
            'data' => $services,
            'total' => $services->count()
        ], 200);
    }
    
    /**
     * Format testimonial response
     *
     * @param Testimonial $testimonial
     * @return array
     */
    private function formatTestimonialResponse(Testimonial $testimonial): array
    {
        return [
            'id' => $testimonial->id,
            'client_name' => $testimonial->client_name,
            'client_position' => $testimonial->client_position,
            'client_company' => $testimonial->client_company,
            'client_avatar' => $testimonial->client_avatar ? asset('storage/' . $testimonial->client_avatar) : null,
            'content' => $testimonial->content,
            'rating' => $testimonial->rating,
            'star_rating' => $testimonial->star_rating,
            'service_used' => $testimonial->service_used,
            'is_featured' => $testimonial->is_featured,
            'is_active' => $testimonial->is_active,
            'order' => $testimonial->order,
            'created_at' => $testimonial->created_at,
            'updated_at' => $testimonial->updated_at
        ];
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceApiController extends Controller
{
    /**
     * Get all services
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getServices(Request $request): JsonResponse
    {
        $query = Service::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('title', 'asc');
        
        // Search by title or tags
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('tags', 'like', '%' . $search . '%')
                  ->orWhere('short_description', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by tags
        if ($request->has('tag')) {
            $query->where('tags', 'like', '%' . $request->tag . '%');
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);
        
        // Format response
        $services->getCollection()->transform(function($service) {
            return $this->formatServiceResponse($service);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Services retrieved successfully',
            'data' => $services->items(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
                'last_page' => $services->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Get single service by ID or slug
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getService($identifier): JsonResponse
    {
        $service = Service::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Service retrieved successfully',
            'data' => $this->formatServiceResponse($service)
        ], 200);
    }
    
    /**
     * Get featured services (if is_featured column exists)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFeaturedServices(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);
        
        $services = Service::where('is_active', true);
        
        // Check if is_featured column exists
        if (\Schema::hasColumn('services', 'is_featured')) {
            $services = $services->where('is_featured', true);
        }
        
        $services = $services->orderBy('order', 'asc')
            ->limit($limit)
            ->get();
        
        $services = $services->map(function($service) {
            return $this->formatServiceResponse($service);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Featured services retrieved successfully',
            'data' => $services,
            'total' => $services->count()
        ], 200);
    }
    
    /**
     * Get all service types
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getServiceTypes(Request $request): JsonResponse
    {
        $query = ServiceType::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc');
        
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $serviceTypes = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Service types retrieved successfully',
            'data' => $serviceTypes,
            'total' => $serviceTypes->count()
        ], 200);
    }
    
    /**
     * Get single service type by ID or slug
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getServiceType($identifier): JsonResponse
    {
        $serviceType = ServiceType::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$serviceType) {
            return response()->json([
                'success' => false,
                'message' => 'Service type not found'
            ], 404);
        }
        
        // Get services for this type (if service_type_id column exists)
        $services = [];
        if (\Schema::hasColumn('services', 'service_type_id')) {
            $services = Service::where('service_type_id', $serviceType->id)
                ->where('is_active', true)
                ->orderBy('order', 'asc')
                ->get()
                ->map(function($service) {
                    return $this->formatServiceResponse($service);
                });
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Service type retrieved successfully',
            'data' => [
                'id' => $serviceType->id,
                'name' => $serviceType->name,
                'slug' => $serviceType->slug,
                'icon' => $serviceType->icon,
                'description' => $serviceType->description,
                'is_active' => $serviceType->is_active,
                'order' => $serviceType->order,
                'services' => $services
            ]
        ], 200);
    }
    
    /**
     * Get all unique tags from services
     *
     * @return JsonResponse
     */
    public function getTags(): JsonResponse
    {
        $services = Service::where('is_active', true)
            ->whereNotNull('tags')
            ->get();
        
        $tags = [];
        foreach ($services as $service) {
            $serviceTags = explode(',', $service->tags);
            foreach ($serviceTags as $tag) {
                $tag = trim($tag);
                if (!empty($tag) && !in_array($tag, $tags)) {
                    $tags[] = $tag;
                }
            }
        }
        
        sort($tags);
        
        return response()->json([
            'success' => true,
            'message' => 'Tags retrieved successfully',
            'data' => $tags,
            'total' => count($tags)
        ], 200);
    }
    
    /**
     * Get services by tag
     *
     * @param string $tag
     * @param Request $request
     * @return JsonResponse
     */
    public function getServicesByTag($tag, Request $request): JsonResponse
    {
        $query = Service::where('is_active', true)
            ->where('tags', 'like', '%' . $tag . '%')
            ->orderBy('order', 'asc');
        
        $perPage = $request->get('per_page', 15);
        $services = $query->paginate($perPage);
        
        $services->getCollection()->transform(function($service) {
            return $this->formatServiceResponse($service);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Services retrieved successfully for tag: ' . $tag,
            'data' => $services->items(),
            'meta' => [
                'current_page' => $services->currentPage(),
                'per_page' => $services->perPage(),
                'total' => $services->total(),
                'last_page' => $services->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Format service response
     *
     * @param Service $service
     * @return array
     */
    private function formatServiceResponse(Service $service): array
    {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'slug' => $service->slug,
            'icon' => $service->icon,
            'short_description' => $service->short_description,
            'description' => $service->description,
            'image' => $service->image ? asset('storage/' . $service->image) : null,
            'features' => is_array($service->features) ? $service->features : json_decode($service->features, true),
            'technologies' => is_array($service->technologies) ? $service->technologies : json_decode($service->technologies, true),
            'tags' => $service->tags ? explode(',', $service->tags) : [],
            'is_active' => $service->is_active,
            'order' => $service->order,
            'created_at' => $service->created_at,
            'updated_at' => $service->updated_at
        ];
    }
}
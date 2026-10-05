<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PortfolioItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PortfolioApiController extends Controller
{
    /**
     * Get all portfolio items
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPortfolio(Request $request): JsonResponse
    {
        $query = PortfolioItem::where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc');
        
        // Search by title or category
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $portfolio = $query->paginate($perPage);
        
        // Format response
        $portfolio->getCollection()->transform(function($item) {
            return $this->formatPortfolioResponse($item);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Portfolio retrieved successfully',
            'data' => $portfolio->items(),
            'meta' => [
                'current_page' => $portfolio->currentPage(),
                'per_page' => $portfolio->perPage(),
                'total' => $portfolio->total(),
                'last_page' => $portfolio->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Get single portfolio item by ID or slug
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getPortfolioItem($identifier): JsonResponse
    {
        $item = PortfolioItem::where('is_active', true)
            ->where(function($q) use ($identifier) {
                $q->where('id', $identifier)
                  ->orWhere('slug', $identifier);
            })
            ->first();
        
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Portfolio item not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Portfolio item retrieved successfully',
            'data' => $this->formatPortfolioResponse($item)
        ], 200);
    }
    
    /**
     * Format portfolio response
     *
     * @param PortfolioItem $item
     * @return array
     */
    private function formatPortfolioResponse(PortfolioItem $item): array
    {
        // Decode JSON fields
        $gallery = [];
        if ($item->gallery) {
            $gallery = is_array($item->gallery) ? $item->gallery : json_decode($item->gallery, true);
        }
        
        $technologies = [];
        if ($item->technologies) {
            $technologies = is_array($item->technologies) ? $item->technologies : json_decode($item->technologies, true);
        }
        
        $challenges = [];
        if ($item->challenges) {
            $challenges = is_array($item->challenges) ? $item->challenges : json_decode($item->challenges, true);
        }
        
        $solutions = [];
        if ($item->solutions) {
            $solutions = is_array($item->solutions) ? $item->solutions : json_decode($item->solutions, true);
        }
        
        $results = [];
        if ($item->results) {
            $results = is_array($item->results) ? $item->results : json_decode($item->results, true);
        }
        
        return [
            'id' => $item->id,
            'title' => $item->title,
            'slug' => $item->slug,
            'category' => $item->category,
            'description' => $item->description,
            'client_name' => $item->client_name,
            'project_url' => $item->project_url,
            'featured_image' => $item->featured_image ? asset('storage/' . $item->featured_image) : null,
            'gallery' => array_map(function($img) {
                return asset('storage/' . $img);
            }, $gallery),
            'technologies' => $technologies,
            'challenges' => $challenges,
            'solutions' => $solutions,
            'results' => $results,
            'completion_date' => $item->completion_date,
            'is_featured' => (bool)$item->is_featured,
            'is_active' => (bool)$item->is_active,
            'order' => $item->order,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at
        ];
    }
}
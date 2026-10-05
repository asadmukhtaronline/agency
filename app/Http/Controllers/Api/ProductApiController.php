<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductApiController extends Controller
{
    /**
     * Get all products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getProducts(Request $request): JsonResponse
    {
        $query = Product::with('category')
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc');
        
        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('short_description', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by category slug
        if ($request->has('category_slug')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);
        
        // Format response
        $products->getCollection()->transform(function($product) {
            return $this->formatProductResponse($product);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Get single product by ID or slug
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getProduct($identifier): JsonResponse
    {
        $product = Product::with('category')
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => $this->formatProductResponse($product)
        ], 200);
    }
    
    /**
     * Get all product categories
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategories(Request $request): JsonResponse
    {
        $query = ProductCategory::where('is_active', true)
            ->orderBy('name', 'asc');
        
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $categories = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Product categories retrieved successfully',
            'data' => $categories,
            'total' => $categories->count()
        ], 200);
    }
    
    /**
     * Get single category by ID or slug with its products
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getCategory($identifier): JsonResponse
    {
        $category = ProductCategory::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        
        // Get products for this category
        $products = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($product) {
                return $this->formatProductResponse($product);
            });
        
        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'is_active' => $category->is_active,
                'products' => $products,
                'total_products' => $products->count()
            ]
        ], 200);
    }
    
    /**
     * Get products by category slug (shortcut)
     *
     * @param string $slug
     * @param Request $request
     * @return JsonResponse
     */
    public function getProductsByCategorySlug($slug, Request $request): JsonResponse
    {
        $category = ProductCategory::where('slug', $slug)
            ->where('is_active', true)
            ->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        
        $query = Product::with('category')
            ->where('category_id', $category->id)
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc');
        
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);
        
        $products->getCollection()->transform(function($product) {
            return $this->formatProductResponse($product);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully for category: ' . $category->name,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon
            ],
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Format product response
     *
     * @param Product $product
     * @return array
     */
    private function formatProductResponse(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'icon' => $product->icon,
            'short_description' => $product->short_description,
            'description' => $product->description,
            'image' => $product->image ? asset('storage/' . $product->image) : null,
            'gallery' => $product->gallery ? array_map(function($img) {
                return asset('storage/' . $img);
            }, json_decode($product->gallery, true) ?? []) : [],
            'features' => is_array($product->features) ? $product->features : json_decode($product->features, true),
            'specifications' => is_array($product->specifications) ? $product->specifications : json_decode($product->specifications, true),
            'tags' => $product->tags,
            'is_active' => $product->is_active,
            'order' => $product->order,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
                'icon' => $product->category->icon
            ] : null,
            'created_at' => $product->created_at,
            'updated_at' => $product->updated_at
        ];
    }
}
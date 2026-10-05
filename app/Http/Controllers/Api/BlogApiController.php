<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BlogApiController extends Controller
{
    /**
     * Get all blog categories
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategories(Request $request): JsonResponse
    {
        $query = BlogCategory::where('is_active', true)
            ->orderBy('id', 'desc');
        
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $categories = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => $categories,
            'total' => $categories->count()
        ], 200);
    }
    
    /**
     * Get single category by ID or slug
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getCategory($identifier): JsonResponse
    {
        $category = BlogCategory::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully',
            'data' => $category
        ], 200);
    }
    
    /**
     * Get all blog posts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPosts(Request $request): JsonResponse
    {
        $query = BlogPost::with('category')
            ->where('is_published', true)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc');
        
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
        
        // Filter by featured posts
        if ($request->has('featured') && $request->featured == true) {
            $query->where('is_featured', true);
        }
        
        // Search by title or content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%')
                  ->orWhere('excerpt', 'like', '%' . $search . '%');
            });
        }
        
        // Filter by tags
        if ($request->has('tag')) {
            $query->where('tags', 'like', '%' . $request->tag . '%');
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $posts = $query->paginate($perPage);
        
        // Format posts with full image URLs
        $posts->getCollection()->transform(function($post) {
            return $this->formatPostResponse($post);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully',
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Get single blog post by ID or slug
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getPost($identifier): JsonResponse
    {
        $post = BlogPost::with('category')
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }
        
        // Increment view count
        $post->increment('views');
        
        return response()->json([
            'success' => true,
            'message' => 'Post retrieved successfully',
            'data' => $this->formatPostResponse($post)
        ], 200);
    }
    
    /**
     * Get related posts
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function getRelatedPosts($id, Request $request): JsonResponse
    {
        $post = BlogPost::find($id);
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 404);
        }
        
        $limit = $request->get('limit', 4);
        
        $relatedPosts = BlogPost::with('category')
            ->where('is_published', true)
            ->where('id', '!=', $id)
            ->where(function($q) use ($post) {
                $q->where('category_id', $post->category_id)
                  ->orWhere('tags', 'like', '%' . $post->tags . '%');
            })
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
        
        $relatedPosts = $relatedPosts->map(function($post) {
            return $this->formatPostResponse($post);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Related posts retrieved successfully',
            'data' => $relatedPosts,
            'total' => $relatedPosts->count()
        ], 200);
    }
    
    /**
     * Get posts by category
     *
     * @param int $categoryId
     * @param Request $request
     * @return JsonResponse
     */
    public function getPostsByCategory($categoryId, Request $request): JsonResponse
    {
        $category = BlogCategory::find($categoryId);
        
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }
        
        $query = BlogPost::with('category')
            ->where('category_id', $categoryId)
            ->where('is_published', true)
            ->orderBy('published_at', 'desc');
        
        $perPage = $request->get('per_page', 15);
        $posts = $query->paginate($perPage);
        
        $posts->getCollection()->transform(function($post) {
            return $this->formatPostResponse($post);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully for category: ' . $category->name,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'color' => $category->color
            ],
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Get featured posts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFeaturedPosts(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);
        
        $posts = BlogPost::with('category')
            ->where('is_published', true)
            ->where('is_featured', true)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
        
        $posts = $posts->map(function($post) {
            return $this->formatPostResponse($post);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Featured posts retrieved successfully',
            'data' => $posts,
            'total' => $posts->count()
        ], 200);
    }
    
    /**
     * Get popular posts (by views)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPopularPosts(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        
        $posts = BlogPost::with('category')
            ->where('is_published', true)
            ->orderBy('views', 'desc')
            ->limit($limit)
            ->get();
        
        $posts = $posts->map(function($post) {
            return $this->formatPostResponse($post);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Popular posts retrieved successfully',
            'data' => $posts,
            'total' => $posts->count()
        ], 200);
    }
    
    /**
     * Get recent posts
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecentPosts(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 5);
        
        $posts = BlogPost::with('category')
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
        
        $posts = $posts->map(function($post) {
            return $this->formatPostResponse($post);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Recent posts retrieved successfully',
            'data' => $posts,
            'total' => $posts->count()
        ], 200);
    }
    
    /**
     * Get all unique tags
     *
     * @return JsonResponse
     */
    public function getTags(): JsonResponse
    {
        $posts = BlogPost::where('is_published', true)
            ->whereNotNull('tags')
            ->get();
        
        $tags = [];
        foreach ($posts as $post) {
            $postTags = explode(',', $post->tags);
            foreach ($postTags as $tag) {
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
     * Get posts by tag
     *
     * @param string $tag
     * @param Request $request
     * @return JsonResponse
     */
    public function getPostsByTag($tag, Request $request): JsonResponse
    {
        $query = BlogPost::with('category')
            ->where('is_published', true)
            ->where('tags', 'like', '%' . $tag . '%')
            ->orderBy('published_at', 'desc');
        
        $perPage = $request->get('per_page', 15);
        $posts = $query->paginate($perPage);
        
        $posts->getCollection()->transform(function($post) {
            return $this->formatPostResponse($post);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Posts retrieved successfully for tag: ' . $tag,
            'data' => $posts->items(),
            'meta' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage()
            ]
        ], 200);
    }
    
    /**
     * Format post response with full URLs
     *
     * @param BlogPost $post
     * @return array
     */
    private function formatPostResponse(BlogPost $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'featured_image' => $post->featured_image ? asset('storage/' . $post->featured_image) : null,
            'gallery' => $post->gallery ? array_map(function($img) {
                return asset('storage/' . $img);
            }, json_decode($post->gallery, true) ?? []) : [],
            'tags' => $post->tags ? explode(',', $post->tags) : [],
            'seo_title' => $post->seo_title,
            'seo_description' => $post->seo_description,
            'views' => $post->views,
            'is_featured' => $post->is_featured,
            'is_published' => $post->is_published,
            'published_at' => $post->published_at,
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at,
            'category' => $post->category ? [
                'id' => $post->category->id,
                'name' => $post->category->name,
                'slug' => $post->category->slug,
                'color' => $post->category->color
            ] : null
        ];
    }
}
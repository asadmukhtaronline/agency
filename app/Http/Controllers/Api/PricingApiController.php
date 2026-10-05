<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\Country;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PricingApiController extends Controller
{
    /**
     * Get all pricing plans
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPricingPlans(Request $request): JsonResponse
    {
        $query = PricingPlan::with(['country', 'serviceType'])
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->orderBy('price', 'asc');
        
        // Filter by country
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        
        // Filter by country code
        if ($request->has('country_code')) {
            $query->whereHas('country', function($q) use ($request) {
                $q->where('code', strtoupper($request->country_code));
            });
        }
        
        // Filter by service type
        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }
        
        // Filter by popular
        if ($request->has('popular') && $request->popular == true) {
            $query->where('is_popular', true);
        }
        
        // Get all or paginated
        if ($request->has('per_page')) {
            $perPage = $request->get('per_page', 15);
            $plans = $query->paginate($perPage);
            
            $plans->getCollection()->transform(function($plan) {
                return $this->formatPricingResponse($plan);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Pricing plans retrieved successfully',
                'data' => $plans->items(),
                'meta' => [
                    'current_page' => $plans->currentPage(),
                    'per_page' => $plans->perPage(),
                    'total' => $plans->total(),
                    'last_page' => $plans->lastPage()
                ]
            ], 200);
        }
        
        $plans = $query->get();
        
        $plans = $plans->map(function($plan) {
            return $this->formatPricingResponse($plan);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Pricing plans retrieved successfully',
            'data' => $plans,
            'total' => $plans->count()
        ], 200);
    }
    
    /**
     * Get single pricing plan by ID or slug
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getPricingPlan($identifier): JsonResponse
    {
        $plan = PricingPlan::with(['country', 'serviceType'])
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();
        
        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Pricing plan not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Pricing plan retrieved successfully',
            'data' => $this->formatPricingResponse($plan)
        ], 200);
    }
    
    /**
     * Get pricing plans by country code
     *
     * @param string $countryCode
     * @param Request $request
     * @return JsonResponse
     */
    public function getPricingByCountry($countryCode, Request $request): JsonResponse
    {
        $country = Country::where('code', strtoupper($countryCode))
            ->where('is_active', true)
            ->first();
        
        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country not found'
            ], 404);
        }
        
        $query = PricingPlan::with(['country', 'serviceType'])
            ->where('country_id', $country->id)
            ->where('is_active', true)
            ->orderBy('order', 'asc');
        
        // Filter by service type
        if ($request->has('service_type_id')) {
            $query->where('service_type_id', $request->service_type_id);
        }
        
        $plans = $query->get();
        
        $plans = $plans->map(function($plan) {
            return $this->formatPricingResponse($plan);
        });
        
        return response()->json([
            'success' => true,
            'message' => "Pricing plans for {$country->name} retrieved successfully",
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'currency_symbol' => $country->currency_symbol,
                'currency_code' => $country->currency_code
            ],
            'data' => $plans,
            'total' => $plans->count()
        ], 200);
    }
    
    /**
     * Get pricing plans by service type
     *
     * @param int $serviceTypeId
     * @param Request $request
     * @return JsonResponse
     */
    public function getPricingByServiceType($serviceTypeId, Request $request): JsonResponse
    {
        $serviceType = ServiceType::where('id', $serviceTypeId)
            ->where('is_active', true)
            ->first();
        
        if (!$serviceType) {
            return response()->json([
                'success' => false,
                'message' => 'Service type not found'
            ], 404);
        }
        
        $query = PricingPlan::with(['country', 'serviceType'])
            ->where('service_type_id', $serviceTypeId)
            ->where('is_active', true)
            ->orderBy('order', 'asc');
        
        // Filter by country
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        
        $plans = $query->get();
        
        $plans = $plans->map(function($plan) {
            return $this->formatPricingResponse($plan);
        });
        
        return response()->json([
            'success' => true,
            'message' => "Pricing plans for {$serviceType->name} retrieved successfully",
            'service_type' => [
                'id' => $serviceType->id,
                'name' => $serviceType->name,
                'slug' => $serviceType->slug,
                'icon' => $serviceType->icon
            ],
            'data' => $plans,
            'total' => $plans->count()
        ], 200);
    }
    
    /**
     * Get popular pricing plans
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPopularPlans(Request $request): JsonResponse
    {
        $query = PricingPlan::with(['country', 'serviceType'])
            ->where('is_active', true)
            ->where('is_popular', true)
            ->orderBy('order', 'asc');
        
        // Filter by country
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        
        // Filter by country code
        if ($request->has('country_code')) {
            $query->whereHas('country', function($q) use ($request) {
                $q->where('code', strtoupper($request->country_code));
            });
        }
        
        $limit = $request->get('limit', 6);
        $plans = $query->limit($limit)->get();
        
        $plans = $plans->map(function($plan) {
            return $this->formatPricingResponse($plan);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Popular pricing plans retrieved successfully',
            'data' => $plans,
            'total' => $plans->count()
        ], 200);
    }
    
    /**
     * Format pricing plan response
     *
     * @param PricingPlan $plan
     * @return array
     */
    private function formatPricingResponse(PricingPlan $plan): array
    {
        // Decode features
        $features = [];
        if ($plan->features) {
            $features = is_array($plan->features) ? $plan->features : json_decode($plan->features, true);
        }
        
        // Format period display
        $periodDisplay = match($plan->period) {
            'month' => 'Monthly',
            'year' => 'Yearly',
            'one-time' => 'One Time',
            default => ucfirst($plan->period)
        };
        
        return [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'icon' => $plan->icon,
            'price' => $plan->price,
            'price_formatted' => $plan->country->currency_symbol . number_format($plan->price, 2),
            'period' => $plan->period,
            'period_display' => $periodDisplay,
            'features' => $features,
            'feature_count' => count($features),
            'is_popular' => $plan->is_popular,
            'button_text' => $plan->button_text,
            'button_link' => $plan->button_link,
            'order' => $plan->order,
            'is_active' => $plan->is_active,
            'country' => $plan->country ? [
                'id' => $plan->country->id,
                'name' => $plan->country->name,
                'code' => $plan->country->code,
                'currency_symbol' => $plan->country->currency_symbol,
                'currency_code' => $plan->country->currency_code
            ] : null,
            'service_type' => $plan->serviceType ? [
                'id' => $plan->serviceType->id,
                'name' => $plan->serviceType->name,
                'slug' => $plan->serviceType->slug,
                'icon' => $plan->serviceType->icon
            ] : null,
            'created_at' => $plan->created_at,
            'updated_at' => $plan->updated_at
        ];
    }
}
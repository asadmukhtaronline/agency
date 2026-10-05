<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CountryApiController extends Controller
{
    /**
     * Get all countries
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCountries(Request $request): JsonResponse
    {
        $query = Country::where('is_active', true)
            ->orderBy('name', 'asc');
        
        // Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }
        
        $countries = $query->get();
        
        return response()->json([
            'success' => true,
            'message' => 'Countries retrieved successfully',
            'data' => $countries,
            'total' => $countries->count()
        ], 200);
    }
    
    /**
     * Get single country by ID or code
     *
     * @param string $identifier
     * @return JsonResponse
     */
    public function getCountry($identifier): JsonResponse
    {
        $country = Country::where('id', $identifier)
            ->orWhere('code', strtoupper($identifier))
            ->first();
        
        if (!$country) {
            return response()->json([
                'success' => false,
                'message' => 'Country not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Country retrieved successfully',
            'data' => [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'currency_code' => $country->currency_code,
                'currency_symbol' => $country->currency_symbol,
                'flag' => $country->flag ? asset('storage/' . $country->flag) : null,
                'phone_number' => $country->phone_number,
                'whatsapp_number' => $country->whatsapp_number,
                'email' => $country->email,
                'is_active' => $country->is_active
            ]
        ], 200);
    }
}
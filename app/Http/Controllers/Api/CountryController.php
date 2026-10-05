<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    /**
     * Get all countries
     */
    public function index(Request $request)
    {
        $query = Country::query();
        
        // Optional: Search by name or code
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
        }
        
        // Optional: Filter only active countries
        if ($request->has('active') && $request->active == true) {
            $query->where('is_active', true);
        }
        
        // Get all countries
        $countries = $query->orderBy('name')->get();
        
        // Format response with flag URL
        $countries = $countries->map(function($country) {
            return [
                'id' => $country->id,
                'name' => $country->name,
                'code' => $country->code,
                'currency_code' => $country->currency_code,
                'currency_symbol' => $country->currency_symbol,
                'flag_url' => $country->flag ? asset('storage/' . $country->flag) : null,
                'phone_number' => $country->phone_number,
                'whatsapp_number' => $country->whatsapp_number,
                'email' => $country->email,
                'is_active' => $country->is_active
            ];
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Countries retrieved successfully',
            'data' => $countries
        ], 200);
    }
}
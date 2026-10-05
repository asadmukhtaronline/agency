<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    // Sirf yeh ek method chahiye - insert karne ke liye
    public function store(Request $request)
    {
        try {
            // Data insert karo
            $quote = QuoteRequest::create([
                'pricing_plan_id' => $request->pricing_plan_id,
                'plan_name' => $request->plan_name,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quote request submitted successfully',
                'data' => $quote
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
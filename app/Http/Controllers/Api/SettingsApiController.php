<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsApiController extends Controller
{
    /**
     * Get all settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllSettings(Request $request): JsonResponse
    {
        $settings = Setting::all();
        
        $data = [];
        foreach ($settings as $setting) {
            $value = $setting->value;
            
            // Format image URLs
            if ($setting->type == 'image' && $value) {
                $value = asset('storage/' . $value);
            }
            
            $data[$setting->key] = $value;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Settings retrieved successfully',
            'data' => $data
        ], 200);
    }
    
    /**
     * Get settings by group
     *
     * @param string $group
     * @return JsonResponse
     */
    public function getSettingsByGroup($group): JsonResponse
    {
        $settings = Setting::where('group', $group)->get();
        
        $data = [];
        foreach ($settings as $setting) {
            $value = $setting->value;
            
            // Format image URLs
            if ($setting->type == 'image' && $value) {
                $value = asset('storage/' . $value);
            }
            
            $data[$setting->key] = $value;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Settings for group '{$group}' retrieved successfully",
            'data' => $data
        ], 200);
    }
    
    /**
     * Get single setting by key
     *
     * @param string $key
     * @return JsonResponse
     */
    public function getSetting($key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }
        
        $value = $setting->value;
        
        // Format image URLs
        if ($setting->type == 'image' && $value) {
            $value = asset('storage/' . $value);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Setting retrieved successfully',
            'data' => [
                'key' => $setting->key,
                'value' => $value,
                'type' => $setting->type,
                'group' => $setting->group
            ]
        ], 200);
    }
    
    /**
     * Get general settings (site info)
     *
     * @return JsonResponse
     */
    public function getGeneralSettings(): JsonResponse
    {
        $keys = [
            'site_name', 'site_title', 'site_description', 'site_keywords',
            'site_logo', 'site_favicon'
        ];
        
        $data = [];
        foreach ($keys as $key) {
            $value = Setting::get($key);
            if (in_array($key, ['site_logo', 'site_favicon']) && $value) {
                $value = asset('storage/' . $value);
            }
            $data[$key] = $value;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'General settings retrieved successfully',
            'data' => $data
        ], 200);
    }
    
    /**
     * Get contact settings
     *
     * @return JsonResponse
     */
    public function getContactSettings(): JsonResponse
    {
        $keys = ['contact_email', 'contact_phone', 'contact_phone_2', 'contact_address', 'contact_map_url'];
        
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = Setting::get($key);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Contact settings retrieved successfully',
            'data' => $data
        ], 200);
    }
    
    /**
     * Get social media settings
     *
     * @return JsonResponse
     */
    public function getSocialSettings(): JsonResponse
    {
        $keys = ['social_facebook', 'social_twitter', 'social_instagram', 'social_linkedin', 'social_youtube', 'social_github'];
        
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = Setting::get($key);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Social media settings retrieved successfully',
            'data' => $data
        ], 200);
    }
    
    /**
     * Get SEO settings
     *
     * @return JsonResponse
     */
    public function getSeoSettings(): JsonResponse
    {
        $keys = ['seo_title', 'seo_description', 'seo_keywords', 'seo_author', 'google_analytics_id', 'google_verification'];
        
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = Setting::get($key);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'SEO settings retrieved successfully',
            'data' => $data
        ], 200);
    }
    
    /**
     * Get business hours
     *
     * @return JsonResponse
     */
    public function getBusinessHours(): JsonResponse
    {
        $keys = ['business_hours', 'business_saturday', 'business_sunday'];
        
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = Setting::get($key);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Business hours retrieved successfully',
            'data' => $data
        ], 200);
    }
    
    /**
     * Get footer settings
     *
     * @return JsonResponse
     */
    public function getFooterSettings(): JsonResponse
    {
        $keys = ['footer_copyright', 'footer_description'];
        
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = Setting::get($key);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Footer settings retrieved successfully',
            'data' => $data
        ], 200);
    }
}
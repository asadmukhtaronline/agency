<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'WebInventorz', 'type' => 'text', 'group' => 'general', 'order' => 1],
            ['key' => 'site_title', 'value' => 'WebInventorz - Digital Agency', 'type' => 'text', 'group' => 'general', 'order' => 2],
            ['key' => 'site_description', 'value' => 'Professional digital agency providing web development, mobile apps, and digital marketing services.', 'type' => 'textarea', 'group' => 'general', 'order' => 3],
            ['key' => 'site_keywords', 'value' => 'web development, mobile apps, digital marketing, SEO, ecommerce', 'type' => 'text', 'group' => 'general', 'order' => 4],
            ['key' => 'site_logo', 'value' => null, 'type' => 'image', 'group' => 'general', 'order' => 5],
            ['key' => 'site_favicon', 'value' => null, 'type' => 'image', 'group' => 'general', 'order' => 6],
            
            // Contact Information
            ['key' => 'contact_email', 'value' => 'info@webinventorz.com', 'type' => 'text', 'group' => 'contact', 'order' => 1],
            ['key' => 'contact_phone', 'value' => '+92 300 1234567', 'type' => 'text', 'group' => 'contact', 'order' => 2],
            ['key' => 'contact_phone_2', 'value' => '+92 300 7654321', 'type' => 'text', 'group' => 'contact', 'order' => 3],
            ['key' => 'contact_address', 'value' => 'Lahore, Pakistan', 'type' => 'textarea', 'group' => 'contact', 'order' => 4],
            ['key' => 'contact_map_url', 'value' => null, 'type' => 'text', 'group' => 'contact', 'order' => 5],
            
            // Social Media Links
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/webinventorz', 'type' => 'text', 'group' => 'social', 'order' => 1],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/webinventorz', 'type' => 'text', 'group' => 'social', 'order' => 2],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/webinventorz', 'type' => 'text', 'group' => 'social', 'order' => 3],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/webinventorz', 'type' => 'text', 'group' => 'social', 'order' => 4],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/webinventorz', 'type' => 'text', 'group' => 'social', 'order' => 5],
            ['key' => 'social_github', 'value' => 'https://github.com/webinventorz', 'type' => 'text', 'group' => 'social', 'order' => 6],
            
            // SEO Settings
            ['key' => 'seo_title', 'value' => 'WebInventorz - Digital Agency | Web Development, Mobile Apps, SEO', 'type' => 'text', 'group' => 'seo', 'order' => 1],
            ['key' => 'seo_description', 'value' => 'Professional digital agency offering web development, mobile app development, digital marketing, and SEO services. Get a free quote today!', 'type' => 'textarea', 'group' => 'seo', 'order' => 2],
            ['key' => 'seo_keywords', 'value' => 'web development, mobile apps, digital marketing, SEO, ecommerce, laravel, react, nextjs', 'type' => 'text', 'group' => 'seo', 'order' => 3],
            ['key' => 'seo_author', 'value' => 'WebInventorz', 'type' => 'text', 'group' => 'seo', 'order' => 4],
            ['key' => 'google_analytics_id', 'value' => null, 'type' => 'text', 'group' => 'seo', 'order' => 5],
            ['key' => 'google_verification', 'value' => null, 'type' => 'text', 'group' => 'seo', 'order' => 6],
            
            // Business Information
            ['key' => 'business_hours', 'value' => 'Mon - Fri: 9:00 AM - 6:00 PM', 'type' => 'text', 'group' => 'business', 'order' => 1],
            ['key' => 'business_saturday', 'value' => 'Sat: 10:00 AM - 2:00 PM', 'type' => 'text', 'group' => 'business', 'order' => 2],
            ['key' => 'business_sunday', 'value' => 'Closed', 'type' => 'text', 'group' => 'business', 'order' => 3],
            
            // Footer Settings
            ['key' => 'footer_copyright', 'value' => '© 2024 WebInventorz. All rights reserved.', 'type' => 'text', 'group' => 'footer', 'order' => 1],
            ['key' => 'footer_description', 'value' => 'Innovative digital solutions for modern businesses.', 'type' => 'textarea', 'group' => 'footer', 'order' => 2],
            
            // Email Settings
            ['key' => 'email_contact', 'value' => 'contact@webinventorz.com', 'type' => 'text', 'group' => 'email', 'order' => 1],
            ['key' => 'email_support', 'value' => 'support@webinventorz.com', 'type' => 'text', 'group' => 'email', 'order' => 2],
            
            // Other Settings
            ['key' => 'timezone', 'value' => 'Asia/Karachi', 'type' => 'text', 'group' => 'other', 'order' => 1],
            ['key' => 'date_format', 'value' => 'M d, Y', 'type' => 'text', 'group' => 'other', 'order' => 2],
        ];
        
        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
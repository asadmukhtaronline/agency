<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkType;

class WorkTypeSeeder extends Seeder
{
    public function run()
    {
        $workTypes = [
            ['name' => 'New Website', 'slug' => 'new-website', 'icon' => 'fas fa-globe', 'color' => '#FE171B', 'order' => 1],
            ['name' => 'Website Redesign', 'slug' => 'website-redesign', 'icon' => 'fas fa-paintbrush', 'color' => '#0dcaf0', 'order' => 2],
            ['name' => 'Bug Fixing', 'slug' => 'bug-fixing', 'icon' => 'fas fa-bug', 'color' => '#ffc107', 'order' => 3],
            ['name' => 'SEO Optimization', 'slug' => 'seo-optimization', 'icon' => 'fas fa-chart-line', 'color' => '#198754', 'order' => 4],
            ['name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'icon' => 'fas fa-megaphone', 'color' => '#6f42c1', 'order' => 5],
            ['name' => 'Mobile App', 'slug' => 'mobile-app', 'icon' => 'fas fa-mobile-alt', 'color' => '#fd7e14', 'order' => 6],
            ['name' => 'E-commerce Setup', 'slug' => 'ecommerce-setup', 'icon' => 'fas fa-shopping-cart', 'color' => '#20c997', 'order' => 7],
            ['name' => 'Website Maintenance', 'slug' => 'website-maintenance', 'icon' => 'fas fa-tools', 'color' => '#6c757d', 'order' => 8],
            ['name' => 'Content Writing', 'slug' => 'content-writing', 'icon' => 'fas fa-pen-fancy', 'color' => '#d63384', 'order' => 9],
            ['name' => 'Logo Design', 'slug' => 'logo-design', 'icon' => 'fas fa-palette', 'color' => '#0d6efd', 'order' => 10],
        ];
        
        foreach ($workTypes as $type) {
            WorkType::create($type);
        }
    }
}
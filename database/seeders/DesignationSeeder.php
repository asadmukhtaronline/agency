<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;

class DesignationSeeder extends Seeder
{
    public function run()
    {
        $designations = [
            ['name' => 'CEO', 'slug' => 'ceo', 'color' => '#FE171B', 'icon' => 'fas fa-crown', 'order' => 1],
            ['name' => 'CTO', 'slug' => 'cto', 'color' => '#0dcaf0', 'icon' => 'fas fa-laptop-code', 'order' => 2],
            ['name' => 'Project Manager', 'slug' => 'project-manager', 'color' => '#198754', 'icon' => 'fas fa-tasks', 'order' => 3],
            ['name' => 'Senior Developer', 'slug' => 'senior-developer', 'color' => '#6f42c1', 'icon' => 'fas fa-code', 'order' => 4],
            ['name' => 'Junior Developer', 'slug' => 'junior-developer', 'color' => '#fd7e14', 'icon' => 'fas fa-code-branch', 'order' => 5],
            ['name' => 'UI/UX Designer', 'slug' => 'ui-ux-designer', 'color' => '#20c997', 'icon' => 'fas fa-paintbrush', 'order' => 6],
            ['name' => 'Sales Manager', 'slug' => 'sales-manager', 'color' => '#d63384', 'icon' => 'fas fa-chart-line', 'order' => 7],
            ['name' => 'Support Executive', 'slug' => 'support-executive', 'color' => '#0d6efd', 'icon' => 'fas fa-headset', 'order' => 8],
        ];
        
        foreach ($designations as $designation) {
            Designation::create($designation);
        }
    }
}
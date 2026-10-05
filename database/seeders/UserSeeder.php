<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@webinventorz.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@webinventorz.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        User::create([
            'name' => 'Editor User',
            'email' => 'editor@webinventorz.com',
            'password' => Hash::make('password123'),
            'role' => 'editor',
            'is_active' => true,
        ]);
        
        User::create([
            'name' => 'Viewer User',
            'email' => 'viewer@webinventorz.com',
            'password' => Hash::make('password123'),
            'role' => 'viewer',
            'is_active' => true,
        ]);
    }
}
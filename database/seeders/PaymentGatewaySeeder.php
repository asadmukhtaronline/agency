<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    public function run()
    {
        $gateways = [
            ['name' => 'PayPal', 'code' => 'paypal', 'icon' => 'fab fa-paypal', 'order' => 1],
            ['name' => 'Stripe', 'code' => 'stripe', 'icon' => 'fab fa-stripe', 'order' => 2],
            ['name' => 'Bank Transfer', 'code' => 'bank_transfer', 'icon' => 'fas fa-university', 'order' => 3],
            ['name' => 'JazzCash', 'code' => 'jazzcash', 'icon' => 'fas fa-mobile-alt', 'order' => 4],
            ['name' => 'EasyPaisa', 'code' => 'easypaisa', 'icon' => 'fas fa-mobile-alt', 'order' => 5],
            ['name' => 'Cash', 'code' => 'cash', 'icon' => 'fas fa-money-bill-wave', 'order' => 6],
        ];
        
        foreach ($gateways as $gateway) {
            PaymentGateway::create($gateway);
        }
    }
}
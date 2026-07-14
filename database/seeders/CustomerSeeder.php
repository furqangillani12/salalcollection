<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Use delete() instead of truncate() because of FK constraint
        Customer::query()->delete();

        Customer::create([
            'name' => 'Walk-in Customer',
            'email' => null,
            'phone' => null,
            'address' => null,
            'customer_type' => 'customer'
        ]);

        Customer::create([
            'name' => 'Reseller One',
            'email' => 'reseller@example.com',
            'phone' => '03001234567',
            'address' => 'Market Road',
            'customer_type' => 'reseller'
        ]);

        Customer::create([
            'name' => 'Wholesale Buyer',
            'email' => 'wholesale@example.com',
            'phone' => '03007654321',
            'address' => 'Industrial Area',
            'customer_type' => 'wholesale'
        ]);
    }
}

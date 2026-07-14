<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;
use App\Models\DispatchMethod;

class PaymentDispatchMethodsSeeder extends Seeder
{
    public function run(): void
    {
        $paymentMethods = [
            ['name' => 'cash',      'label' => 'Cash',      'sort_order' => 0],
            ['name' => 'jazzcash',  'label' => 'Jazz',      'sort_order' => 1],
            ['name' => 'easypaisa', 'label' => 'Easy',      'sort_order' => 2],
            ['name' => 'bank',      'label' => 'Bank',      'sort_order' => 3],
            ['name' => 'cod',       'label' => 'COD',       'sort_order' => 4],
            ['name' => 'pending',   'label' => 'Pending',   'sort_order' => 5],
        ];

        foreach ($paymentMethods as $pm) {
            PaymentMethod::firstOrCreate(['name' => $pm['name']], $pm);
        }

        $dispatchMethods = [
            ['name' => 'Self Pickup', 'has_tracking' => false, 'sort_order' => 0],
            ['name' => 'By Bus',      'has_tracking' => false, 'sort_order' => 1],
            ['name' => 'TCS',         'has_tracking' => true,  'sort_order' => 2],
            ['name' => 'Pak Post',    'has_tracking' => true,  'sort_order' => 3],
            ['name' => 'PostEx',      'has_tracking' => true,  'sort_order' => 4],
        ];

        foreach ($dispatchMethods as $dm) {
            DispatchMethod::firstOrCreate(['name' => $dm['name']], $dm);
        }
    }
}

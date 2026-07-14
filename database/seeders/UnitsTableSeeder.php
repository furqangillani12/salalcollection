<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitsTableSeeder extends Seeder
{
    public function run()
    {
        $units = [
            [
                'name' => 'Piece',
                'abbreviation' => 'pcs',
                'description' => 'Single item or unit',
                'is_active' => true
            ],
            [
                'name' => 'Dozen',
                'abbreviation' => 'dz',
                'description' => '12 pieces',
                'is_active' => true
            ],
            [
                'name' => 'Kilogram',
                'abbreviation' => 'kg',
                'description' => 'Weight measurement',
                'is_active' => true
            ],
            [
                'name' => 'Gram',
                'abbreviation' => 'g',
                'description' => 'Weight measurement',
                'is_active' => true
            ],
            [
                'name' => 'Liter',
                'abbreviation' => 'L',
                'description' => 'Volume measurement',
                'is_active' => true
            ],
            [
                'name' => 'Milliliter',
                'abbreviation' => 'mL',
                'description' => 'Volume measurement',
                'is_active' => true
            ],
            [
                'name' => 'Meter',
                'abbreviation' => 'm',
                'description' => 'Length measurement',
                'is_active' => true
            ],
            [
                'name' => 'Centimeter',
                'abbreviation' => 'cm',
                'description' => 'Length measurement',
                'is_active' => true
            ],
            [
                'name' => 'Box',
                'abbreviation' => 'box',
                'description' => 'Complete box',
                'is_active' => true
            ],
            [
                'name' => 'Packet',
                'abbreviation' => 'pkt',
                'description' => 'Packet of items',
                'is_active' => true
            ],
            [
                'name' => 'Carton',
                'abbreviation' => 'ctn',
                'description' => 'Carton box',
                'is_active' => true
            ],
            [
                'name' => 'Pair',
                'abbreviation' => 'pr',
                'description' => 'Two items together',
                'is_active' => true
            ],
            [
                'name' => 'Set',
                'abbreviation' => 'set',
                'description' => 'Complete set of items',
                'is_active' => true
            ],
            [
                'name' => 'Bottle',
                'abbreviation' => 'btl',
                'description' => 'Bottle container',
                'is_active' => true
            ],
            [
                'name' => 'Can',
                'abbreviation' => 'can',
                'description' => 'Can container',
                'is_active' => true
            ],
            [
                'name' => 'Roll',
                'abbreviation' => 'roll',
                'description' => 'Roll of material',
                'is_active' => true
            ],
            [
                'name' => 'Sheet',
                'abbreviation' => 'sheet',
                'description' => 'Single sheet',
                'is_active' => true
            ],
            [
                'name' => 'Bag',
                'abbreviation' => 'bag',
                'description' => 'Bag of items',
                'is_active' => true
            ],
            [
                'name' => 'Pack',
                'abbreviation' => 'pack',
                'description' => 'Pack of items',
                'is_active' => true
            ],
            [
                'name' => 'Unit',
                'abbreviation' => 'unit',
                'description' => 'Generic unit',
                'is_active' => true
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
        
        $this->command->info('✅ 20 units seeded successfully!');
    }
}
<?php

namespace Database\Seeders;

use App\Models\ShoeType;
use Illuminate\Database\Seeder;

class ShoeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shoeTypes = [
            ['shoe_name' => 'Angel'],
            ['shoe_name' => 'Bee'],
            ['shoe_name' => 'Safeshoe'],
            ['shoe_name' => 'Eshoe'],
            ['shoe_name' => 'LT'],
            ['shoe_name' => 'Ideal'],
        ];

        foreach ($shoeTypes as $type) {
            ShoeType::firstOrCreate(['shoe_name' => $type['shoe_name']], $type);
        }
    }
}

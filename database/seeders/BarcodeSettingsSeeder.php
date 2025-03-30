<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarcodeSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('barcode_settings')->insert([
            'sku' => false,
            'product_name' => false,
            'description' => false,
            'units' => false,
            'category' => false,
            'sub_category' => false,
            'manufacturer' => false,
            'vendor' => false,
            'model' => false,
            'returnable' => false,
            'cost_price' => false,
            'selling_cost' => false,
            'weight' => false,
            'weight_unit' => false,
            'length' => false,
            'width' => false,
            'depth' => false,
            'measurement_unit' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

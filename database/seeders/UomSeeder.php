<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// database/seeders/UomSeeder.php
use App\Models\UomCategory;
use App\Models\UomUnit;

class UomSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'Length' => [
                ['name' => 'meter', 'abbreviation' => 'm'],
                ['name' => 'kilometer', 'abbreviation' => 'km'],
                ['name' => 'centimeter', 'abbreviation' => 'cm'],
                ['name' => 'millimeter', 'abbreviation' => 'mm'],
                ['name' => 'inch', 'abbreviation' => 'in'],
                ['name' => 'foot', 'abbreviation' => 'ft'],
                ['name' => 'mile', 'abbreviation' => 'mi'],
            ],
            'Mass' => [
                ['name' => 'kilogram', 'abbreviation' => 'kg'],
                ['name' => 'gram', 'abbreviation' => 'g'],
                ['name' => 'milligram', 'abbreviation' => 'mg'],
                ['name' => 'pound', 'abbreviation' => 'lb'],
                ['name' => 'ounce', 'abbreviation' => 'oz'],
            ],
            'Time' => [
                ['name' => 'second', 'abbreviation' => 's'],
                ['name' => 'minute', 'abbreviation' => 'min'],
                ['name' => 'hour', 'abbreviation' => 'h'],
                ['name' => 'day', 'abbreviation' => 'd'],
            ],
            'Temperature' => [
                ['name' => 'celsius', 'abbreviation' => '°C'],
                ['name' => 'fahrenheit', 'abbreviation' => '°F'],
                ['name' => 'kelvin', 'abbreviation' => 'K'],
            ],
            'Volume' => [
                ['name' => 'liter', 'abbreviation' => 'l'],
                ['name' => 'milliliter', 'abbreviation' => 'ml'],
                ['name' => 'gallon', 'abbreviation' => 'gal'],
                ['name' => 'cubic meter', 'abbreviation' => 'm³'],
            ],
            'Area' => [
                ['name' => 'square meter', 'abbreviation' => 'm²'],
                ['name' => 'square kilometer', 'abbreviation' => 'km²'],
                ['name' => 'square foot', 'abbreviation' => 'ft²'],
                ['name' => 'square mile', 'abbreviation' => 'mi²'],
                ['name' => 'hectare', 'abbreviation' => 'ha'],
                ['name' => 'acre', 'abbreviation' => 'ac'],
            ],
            'Speed' => [
                ['name' => 'meter per second', 'abbreviation' => 'm/s'],
                ['name' => 'kilometer per hour', 'abbreviation' => 'km/h'],
                ['name' => 'mile per hour', 'abbreviation' => 'mph'],
                ['name' => 'knot', 'abbreviation' => 'kn'],
            ],
            'Energy' => [
                ['name' => 'joule', 'abbreviation' => 'J'],
                ['name' => 'kilojoule', 'abbreviation' => 'kJ'],
                ['name' => 'calorie', 'abbreviation' => 'cal'],
                ['name' => 'kilocalorie', 'abbreviation' => 'kcal'],
                ['name' => 'watt-hour', 'abbreviation' => 'Wh'],
                ['name' => 'kilowatt-hour', 'abbreviation' => 'kWh'],
            ],
            'Pressure' => [
                ['name' => 'pascal', 'abbreviation' => 'Pa'],
                ['name' => 'bar', 'abbreviation' => 'bar'],
                ['name' => 'atmosphere', 'abbreviation' => 'atm'],
                ['name' => 'pound per square inch', 'abbreviation' => 'psi'],
            ],
            'Power' => [
                ['name' => 'watt', 'abbreviation' => 'W'],
                ['name' => 'kilowatt', 'abbreviation' => 'kW'],
                ['name' => 'horsepower', 'abbreviation' => 'hp'],
            ],
            'Weight' => [
                ['name' => 'tonne', 'abbreviation' => 't'],
                ['name' => 'stone', 'abbreviation' => 'st'],
            ],
            'Surface' => [
                ['name' => 'square meter', 'abbreviation' => 'm²'],
                ['name' => 'square kilometer', 'abbreviation' => 'km²'],
                ['name' => 'square foot', 'abbreviation' => 'ft²'],
                ['name' => 'acre', 'abbreviation' => 'ac'],
                ['name' => 'hectare', 'abbreviation' => 'ha'],
            ],
            'Unit' => [
                ['name' => 'piece', 'abbreviation' => 'pc'],
                ['name' => 'dozen', 'abbreviation' => 'dz'],
                ['name' => 'pair', 'abbreviation' => 'pr'],
                ['name' => 'set', 'abbreviation' => 'set'],
            ],
            'Working Day' => [
                ['name' => 'day', 'abbreviation' => 'd'],
                ['name' => 'week', 'abbreviation' => 'w'],
                ['name' => 'month', 'abbreviation' => 'mo'],
                ['name' => 'year', 'abbreviation' => 'y'],
            ],
            // Add more categories and units as needed
        ];

        foreach ($data as $categoryName => $units) {
            $category = UomCategory::create(['name' => $categoryName]);

            foreach ($units as $unit) {
                UomUnit::create([
                    'uom_category_id' => $category->id,
                    'unit_name' => $unit['name'],
                    'abbreviation' => $unit['abbreviation'],
                ]);
            }
        }
    }
}

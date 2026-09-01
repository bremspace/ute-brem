<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CustomerGroup;
use App\Models\Location;
use App\Models\LocationRack;
use App\Models\ProductMaker;
use App\Models\ProductType;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $subCategories = [
            'lcd-touchscreen' => ['OLED', 'Incell', 'Touchscreen'],
            'baterai' => ['Baterai Original Grade', 'Baterai Double Power', 'Baterai Tanam'],
            'backdoor-casing' => ['Backdoor', 'Frame', 'Housing'],
            'flexibel-tombol' => ['Flexibel Power', 'Flexibel Volume', 'Flexibel Fingerprint'],
            'kamera' => ['Kamera Depan', 'Kamera Belakang', 'Kaca Kamera'],
            'konektor-charging' => ['Board Charger', 'Port Charger', 'Mic Flexibel'],
        ];

        foreach ($subCategories as $categorySlug => $items) {
            $category = Category::where('slug', $categorySlug)->first();

            if (! $category) {
                continue;
            }

            foreach ($items as $name) {
                SubCategory::updateOrCreate(
                    ['slug' => Str::slug($category->slug . '-' . $name)],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'is_active' => true,
                    ]
                );
            }
        }

        $brands = [
            'Xiaomi' => ['Redmi Note 10', 'Redmi 9A', 'Redmi Note 11'],
            'Samsung' => ['A12', 'A13', 'A14'],
            'Oppo' => ['A3S', 'A5 2020', 'A16'],
            'Vivo' => ['Y12', 'Y15', 'Y20'],
            'iPhone' => ['iPhone XR', 'iPhone 11', 'iPhone 12'],
        ];

        foreach ($brands as $brandName => $types) {
            $brand = Brand::updateOrCreate(
                ['slug' => Str::slug($brandName)],
                ['name' => $brandName, 'is_active' => true]
            );

            foreach ($types as $typeName) {
                ProductType::updateOrCreate(
                    ['slug' => Str::slug($brandName . '-' . $typeName)],
                    [
                        'brand_id' => $brand->id,
                        'name' => $typeName,
                        'is_active' => true,
                    ]
                );
            }
        }

        foreach (['BPE', 'Vizz', 'Hippo', 'Rakkipanda', 'OEM Premium'] as $makerName) {
            ProductMaker::updateOrCreate(
                ['slug' => Str::slug($makerName)],
                ['name' => $makerName, 'is_active' => true]
            );
        }

        foreach ([
            ['name' => 'Gudang', 'code' => 'GDG', 'racks' => ['Rak 1', 'Rak 2']],
            ['name' => 'Etalase', 'code' => 'ETL', 'racks' => ['Rak 1', 'Rak 2']],
            ['name' => 'Toko', 'code' => 'TOKO', 'racks' => ['Rak 1', 'Rak 2', 'Rak 3']],
        ] as $location) {
            $row = Location::updateOrCreate(
                ['code' => $location['code']],
                ['name' => $location['name'], 'is_active' => true]
            );

            foreach ($location['racks'] as $rackName) {
                LocationRack::updateOrCreate(
                    ['location_id' => $row->id, 'name' => $rackName],
                    ['code' => strtoupper($row->code . '-' . str($rackName)->slug('-')), 'is_active' => true]
                );
            }
        }

        foreach ([
            ['name' => 'Group 1', 'sort_order' => 1],
            ['name' => 'Group 2', 'sort_order' => 2],
            ['name' => 'Group 3', 'sort_order' => 3],
            ['name' => 'Group 4', 'sort_order' => 4],
        ] as $group) {
            CustomerGroup::updateOrCreate(
                ['slug' => Str::slug($group['name'])],
                [
                    'name' => $group['name'],
                    'sort_order' => $group['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}

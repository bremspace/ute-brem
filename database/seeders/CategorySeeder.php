<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating categories...');

        $categories = [
            [
                'name' => 'LCD & Touchscreen',
                'slug' => 'lcd-touchscreen',
                'description' => 'Layar LCD, AMOLED, dan touchscreen untuk berbagai tipe HP.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Baterai',
                'slug' => 'baterai',
                'description' => 'Baterai tanam dan baterai original-compatible untuk smartphone.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Backdoor & Casing',
                'slug' => 'backdoor-casing',
                'description' => 'Backdoor, housing, frame, dan casing pengganti HP.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Flexibel & Tombol',
                'slug' => 'flexibel-tombol',
                'description' => 'Flexibel power, volume, fingerprint, dan sparepart tombol lainnya.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Kamera',
                'slug' => 'kamera',
                'description' => 'Kamera depan, belakang, dan modul kamera smartphone.',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Konektor Charging',
                'slug' => 'konektor-charging',
                'description' => 'Board charger, port charging, dan konektor pendukung.',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->info(count($categories) . ' categories created successfully!');
    }
}

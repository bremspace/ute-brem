<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        $rows = [
            ['JS-SW-001', 'Install Ulang Software Android', 'Software', 'Android', 75000, true, false],
            ['JS-SW-002', 'Flash Firmware iPhone', 'Software', 'iPhone', 150000, true, false],
            ['JS-HW-001', 'Jasa Ganti LCD', 'Hardware', 'Display', 100000, true, true],
            ['JS-HW-002', 'Jasa Ganti Baterai', 'Hardware', 'Power', 50000, false, true],
            ['JS-HW-003', 'Jasa Ganti Konektor Charger', 'Hardware', 'Charging', 125000, true, true],
            ['JS-CHK-001', 'Cek Kerusakan HP', 'Pemeriksaan', 'Diagnosa', 25000, false, true],
            ['JS-CLN-001', 'Cleaning Mesin Ringan', 'Perawatan', 'Cleaning', 60000, false, true],
            ['JS-DATA-001', 'Backup dan Restore Data', 'Data', 'Backup', 100000, true, false],
        ];

        foreach ($rows as [$code, $name, $category, $group, $price, $openPrice, $taxable]) {
            Service::updateOrCreate(
                ['service_code' => $code],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'category' => $category,
                    'group' => $group,
                    'price_toko' => $price,
                    'price_partai' => max(0, $price - 10000),
                    'price_cabang' => max(0, $price - 15000),
                    'is_taxable' => $taxable,
                    'is_open_price' => $openPrice,
                    'allow_discount_override' => true,
                    'is_published' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}

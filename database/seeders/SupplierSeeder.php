<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'CV Mitra LCD Nusantara', 'code' => 'SUP-LCD', 'phone' => '081234567801', 'contact_person' => 'Andi', 'address' => 'Jakarta Barat'],
            ['name' => 'PT Baterai Mobile Indo', 'code' => 'SUP-BAT', 'phone' => '081234567802', 'contact_person' => 'Rina', 'address' => 'Jakarta Utara'],
            ['name' => 'Sinar Sparepart Gadget', 'code' => 'SUP-SPG', 'phone' => '081234567803', 'contact_person' => 'Budi', 'address' => 'Surabaya'],
            ['name' => 'Galaxy Part Center', 'code' => 'SUP-GPC', 'phone' => '081234567804', 'contact_person' => 'Nando', 'address' => 'Bandung'],
        ] as $supplier) {
            Supplier::updateOrCreate(
                ['code' => $supplier['code']],
                [
                    'name' => $supplier['name'],
                    'slug' => Str::slug($supplier['name']),
                    'phone' => $supplier['phone'],
                    'contact_person' => $supplier['contact_person'],
                    'address' => $supplier['address'],
                    'is_active' => true,
                ]
            );
        }
    }
}

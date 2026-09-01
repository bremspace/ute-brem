<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MasterBarangSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding master barang...');

        $this->call([
            CategorySeeder::class,
            ProductReferenceSeeder::class,
            SupplierSeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
            StockSeeder::class,
        ]);

        $this->command->info('Master barang seeded successfully.');
    }
}

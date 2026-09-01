<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['PCS', 'SET', 'PACK', 'DUS', 'ROLL', 'LEMBAR'] as $unit) {
            Unit::updateOrCreate(
                ['name' => $unit],
                ['code' => $unit, 'is_active' => true]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\ShippingProvider;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ShippingProviderSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('shipping_providers')) {
            return;
        }

        $providers = [
            ['name' => 'Gojek GoSend', 'code' => 'gojek', 'type' => 'instant', 'api_config' => ['integration' => 'api', 'service' => 'gosend']],
            ['name' => 'Grab GrabExpress', 'code' => 'grab', 'type' => 'instant', 'api_config' => ['integration' => 'api', 'service' => 'grabexpress']],
            ['name' => 'Maxim', 'code' => 'maxim', 'type' => 'instant', 'api_config' => ['integration' => 'manual', 'service' => 'maxim']],
            ['name' => 'J&T Express', 'code' => 'jnt', 'type' => 'express', 'api_config' => ['integration' => 'api', 'service' => 'jnt']],
            ['name' => 'JNE', 'code' => 'jne', 'type' => 'regular', 'api_config' => ['integration' => 'api', 'service' => 'jne']],
            ['name' => 'SiCepat', 'code' => 'sicepat', 'type' => 'express', 'api_config' => ['integration' => 'api', 'service' => 'sicepat']],
        ];

        foreach ($providers as $data) {
            ShippingProvider::updateOrCreate(['code' => $data['code']], $data);
        }

        $this->seedRates();
    }

    private function seedRates(): void
    {
        if (! Schema::hasTable('shipping_rates')) {
            return;
        }

        // [provider_code, origin, destination, min_weight, max_weight, base_rate, per_kg_rate, estimated_days]
        $rates = [
            ['jnt', 'Jakarta', 'Bandung', 0, 1, 8000, 0, '1–2 hari'],
            ['jnt', 'Jakarta', 'Bandung', 1, 5, 8000, 3000, '1–2 hari'],
            ['jnt', 'Jakarta', 'Surabaya', 0, 1, 10000, 0, '2–3 hari'],
            ['jnt', 'Jakarta', 'Surabaya', 1, 5, 10000, 4000, '2–3 hari'],
            ['jnt', 'Jakarta', 'Medan', 0, 1, 15000, 0, '2–3 hari'],
            ['jnt', 'Jakarta', 'Medan', 1, 5, 15000, 6000, '2–3 hari'],
            ['jne', 'Jakarta', 'Bandung', 0, 1, 7000, 0, '2–3 hari'],
            ['jne', 'Jakarta', 'Bandung', 1, 5, 7000, 2500, '2–3 hari'],
            ['jne', 'Jakarta', 'Surabaya', 0, 1, 9000, 0, '2–3 hari'],
            ['jne', 'Jakarta', 'Surabaya', 1, 5, 9000, 3500, '2–3 hari'],
            ['jne', 'Jakarta', 'Medan', 0, 1, 14000, 0, '3–5 hari'],
            ['jne', 'Jakarta', 'Medan', 1, 5, 14000, 5500, '3–5 hari'],
            ['sicepat', 'Jakarta', 'Bandung', 0, 1, 7500, 0, '1–2 hari'],
            ['sicepat', 'Jakarta', 'Bandung', 1, 5, 7500, 2800, '1–2 hari'],
            ['sicepat', 'Jakarta', 'Surabaya', 0, 1, 9500, 0, '2–3 hari'],
            ['sicepat', 'Jakarta', 'Surabaya', 1, 5, 9500, 3800, '2–3 hari'],
            ['sicepat', 'Jakarta', 'Medan', 0, 1, 14500, 0, '2–3 hari'],
            ['sicepat', 'Jakarta', 'Medan', 1, 5, 14500, 5800, '2–3 hari'],
        ];

        foreach ($rates as [$code, $origin, $destination, $minWeight, $maxWeight, $baseRate, $perKgRate, $estimatedDays]) {
            $provider = ShippingProvider::where('code', $code)->first();

            if (! $provider) {
                continue;
            }

            ShippingRate::updateOrCreate(
                [
                    'shipping_provider_id' => $provider->id,
                    'origin_city' => $origin,
                    'destination_city' => $destination,
                    'min_weight' => $minWeight,
                ],
                [
                    'max_weight' => $maxWeight,
                    'base_rate' => $baseRate,
                    'per_kg_rate' => $perKgRate,
                    'estimated_days' => $estimatedDays,
                    'is_active' => true,
                ],
            );
        }
    }
}
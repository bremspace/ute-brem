<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Services\StockLedgerService;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding stock ledger data...');

        $service = app(StockLedgerService::class);

        $gudang = Location::where('code', 'GDG')->first();
        $toko = Location::where('code', 'TOKO')->first();
        $etalase = Location::where('code', 'ETL')->first();

        if (! $gudang || ! $toko || ! $etalase) {
            $this->command->warn('Lokasi stok belum lengkap. Jalankan ProductReferenceSeeder lebih dulu.');
            return;
        }

        $plans = [
            'LCD-IP11-OLED-001' => [
                'opening' => [['location' => $gudang, 'qty' => 12]],
                'transfers' => [
                    ['from' => $gudang, 'to' => $toko, 'qty' => 5, 'notes' => 'Display awal LCD iPhone 11'],
                    ['from' => $gudang, 'to' => $etalase, 'qty' => 2, 'notes' => 'Stok etalase premium'],
                ],
                'movements' => [['location' => $toko, 'type' => StockLedgerService::TYPE_DAMAGED_IN, 'qty' => 1, 'notes' => 'Panel retak saat quality check']],
            ],
            'BAT-SAM-A12-DP-001' => [
                'opening' => [['location' => $gudang, 'qty' => 80]],
                'transfers' => [
                    ['from' => $gudang, 'to' => $toko, 'qty' => 25, 'notes' => 'Stok fast moving toko'],
                    ['from' => $gudang, 'to' => $etalase, 'qty' => 10, 'notes' => 'Display baterai Samsung'],
                ],
                'movements' => [['location' => $gudang, 'type' => StockLedgerService::TYPE_IN, 'qty' => 15, 'notes' => 'Barang datang tambahan supplier']],
            ],
            'BD-RN10-BLK-001' => [
                'opening' => [['location' => $gudang, 'qty' => 35]],
                'transfers' => [
                    ['from' => $gudang, 'to' => $toko, 'qty' => 12, 'notes' => 'Stok backdoor untuk penjualan toko'],
                    ['from' => $gudang, 'to' => $etalase, 'qty' => 3, 'notes' => 'Display backdoor'],
                ],
                'movements' => [['location' => $toko, 'type' => StockLedgerService::TYPE_OUT, 'qty' => 2, 'notes' => 'Pemakaian servis internal']],
            ],
            'BC-OPPO-A3S-001' => [
                'opening' => [['location' => $gudang, 'qty' => 120]],
                'transfers' => [
                    ['from' => $gudang, 'to' => $toko, 'qty' => 30, 'notes' => 'Stok board charger toko'],
                    ['from' => $gudang, 'to' => $etalase, 'qty' => 10, 'notes' => 'Stok etalase board charger'],
                ],
                'movements' => [['location' => $gudang, 'type' => StockLedgerService::TYPE_DAMAGED_IN, 'qty' => 3, 'notes' => 'Konektor patah saat QC']],
            ],
            'FLX-PWR-Y12-001' => [
                'opening' => [['location' => $gudang, 'qty' => 60]],
                'transfers' => [
                    ['from' => $gudang, 'to' => $toko, 'qty' => 20, 'notes' => 'Stok fleksibel untuk servis toko'],
                    ['from' => $gudang, 'to' => $etalase, 'qty' => 8, 'notes' => 'Stok etalase fleksibel'],
                ],
                'movements' => [['location' => $toko, 'type' => StockLedgerService::TYPE_OUT, 'qty' => 5, 'notes' => 'Pemakaian repair harian']],
            ],
        ];

        foreach ($plans as $sku => $plan) {
            $product = Product::where('sku', $sku)->first();

            if (! $product) {
                continue;
            }

            StockMovement::where('product_id', $product->id)->delete();
            StockTransfer::where('product_id', $product->id)->delete();
            ProductStock::where('product_id', $product->id)->delete();

            foreach ($plan['opening'] as $opening) {
                $service->applyMovement(
                    $product,
                    $opening['location'],
                    StockLedgerService::TYPE_OPENING,
                    $opening['qty'],
                    'Saldo awal seed'
                );
            }

            foreach ($plan['transfers'] as $transfer) {
                $service->transfer(
                    $product,
                    $transfer['from'],
                    $transfer['to'],
                    $transfer['qty'],
                    $transfer['notes']
                );
            }

            foreach ($plan['movements'] as $movement) {
                $service->applyMovement(
                    $product,
                    $movement['location'],
                    $movement['type'],
                    $movement['qty'],
                    $movement['notes']
                );
            }
        }

        $this->command->info('Stock ledger data seeded successfully.');
    }
}

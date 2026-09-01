<?php

namespace App\Services;

use App\Models\Location;
use App\Models\LocationRack;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    public const TYPE_OPENING = 'opening';
    public const TYPE_IN = 'in';
    public const TYPE_OUT = 'out';
    public const TYPE_ADJUSTMENT_PLUS = 'adjustment_plus';
    public const TYPE_ADJUSTMENT_MINUS = 'adjustment_minus';
    public const TYPE_DAMAGED_IN = 'damaged_in';
    public const TYPE_DAMAGED_OUT = 'damaged_out';
    public const TYPE_RECOVER_DAMAGED = 'recover_damaged';
    public const TYPE_TRANSFER_IN = 'transfer_in';
    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public function movementOptions(): array
    {
        return [
            self::TYPE_OPENING => 'Saldo Awal',
            self::TYPE_IN => 'Stok Masuk',
            self::TYPE_OUT => 'Stok Keluar',
            self::TYPE_ADJUSTMENT_PLUS => 'Adjustment Tambah',
            self::TYPE_ADJUSTMENT_MINUS => 'Adjustment Kurang',
            self::TYPE_DAMAGED_IN => 'Jadikan Rusak',
            self::TYPE_DAMAGED_OUT => 'Buang Stok Rusak',
            self::TYPE_RECOVER_DAMAGED => 'Recovery dari Rusak',
        ];
    }

    public function applyMovement(
        Product $product,
        Location $location,
        string $movementType,
        float $quantity,
        ?string $notes = null,
        $movementAt = null,
        ?string $referenceType = null,
        ?string $referenceCode = null,
        ?LocationRack $rack = null
    ): StockMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity mutasi harus lebih dari 0.');
        }

        [$goodDelta, $damagedDelta] = $this->resolveDeltas($movementType, $quantity);

        return DB::transaction(function () use (
            $product,
            $location,
            $movementType,
            $quantity,
            $notes,
            $movementAt,
            $referenceType,
            $referenceCode,
            $rack,
            $goodDelta,
            $damagedDelta
        ) {
            $stock = ProductStock::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                ],
                [
                    'quantity' => 0,
                    'damaged_quantity' => 0,
                ]
            );

            $stockBefore = (float) $stock->quantity;
            $damagedBefore = (float) $stock->damaged_quantity;
            $stockAfter = $stockBefore + $goodDelta;
            $damagedAfter = $damagedBefore + $damagedDelta;

            if ($stockAfter < 0) {
                throw new \RuntimeException("Stok lokasi {$location->name} tidak mencukupi.");
            }

            if ($damagedAfter < 0) {
                throw new \RuntimeException("Stok rusak lokasi {$location->name} tidak mencukupi.");
            }

            $stock->update([
                'quantity' => $stockAfter,
                'damaged_quantity' => $damagedAfter,
                'location_rack_id' => $rack?->id ?: $stock->location_rack_id,
            ]);

            $movement = StockMovement::create([
                'product_id' => $product->id,
                'location_id' => $location->id,
                'location_rack_id' => $rack?->id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'good_delta' => $goodDelta,
                'damaged_delta' => $damagedDelta,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'damaged_before' => $damagedBefore,
                'damaged_after' => $damagedAfter,
                'reference_type' => $referenceType,
                'reference_code' => $referenceCode,
                'notes' => $notes,
                'movement_at' => $movementAt ?: now(),
                'created_by' => auth()->id(),
            ]);

            $this->syncProductTotals($product);

            return $movement;
        });
    }

    public function transfer(
        Product $product,
        Location $sourceLocation,
        Location $targetLocation,
        float $quantity,
        ?string $notes = null,
        $transferredAt = null,
        ?LocationRack $sourceRack = null,
        ?LocationRack $targetRack = null
    ): StockTransfer {
        if ($sourceLocation->is($targetLocation)) {
            throw new \InvalidArgumentException('Lokasi asal dan tujuan harus berbeda.');
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity transfer harus lebih dari 0.');
        }

        return DB::transaction(function () use ($product, $sourceLocation, $targetLocation, $quantity, $notes, $transferredAt, $sourceRack, $targetRack) {
            $transfer = StockTransfer::create([
                'product_id' => $product->id,
                'transfer_code' => $this->generateTransferCode($product),
                'source_location_id' => $sourceLocation->id,
                'source_location_rack_id' => $sourceRack?->id,
                'target_location_id' => $targetLocation->id,
                'target_location_rack_id' => $targetRack?->id,
                'quantity' => $quantity,
                'notes' => $notes,
                'transferred_at' => $transferredAt ?: now(),
                'created_by' => auth()->id(),
            ]);

            $referenceCode = $transfer->transfer_code;

            $this->applyMovement(
                $product,
                $sourceLocation,
                self::TYPE_TRANSFER_OUT,
                $quantity,
                $notes,
                $transfer->transferred_at,
                'transfer',
                $referenceCode,
                $sourceRack
            );

            $this->applyMovement(
                $product,
                $targetLocation,
                self::TYPE_TRANSFER_IN,
                $quantity,
                $notes,
                $transfer->transferred_at,
                'transfer',
                $referenceCode,
                $targetRack
            );

            $this->syncProductTotals($product);

            return $transfer;
        });
    }

    public function updateStockMetadata(Product $product, array $rows): void
    {
        DB::transaction(function () use ($product, $rows) {
            foreach ($rows as $row) {
                $stock = ProductStock::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'location_id' => $row['location_id'],
                    ],
                    [
                        'quantity' => 0,
                        'damaged_quantity' => 0,
                    ]
                );

                $stock->update([
                    'location_rack_id' => $row['location_rack_id'] ?? $stock->location_rack_id,
                    'stock_min' => $row['stock_min'] ?? null,
                    'stock_max' => $row['stock_max'] ?? null,
                    'notes' => $row['notes'] ?? null,
                ]);
            }
        });
    }

    public function syncProductTotals(Product $product): void
    {
        $product->load('stocks');

        $product->update([
            'stock_global' => $product->stocks->sum(fn ($stock) => (float) $stock->quantity),
            'damaged_stock' => $product->stocks->sum(fn ($stock) => (float) $stock->damaged_quantity),
        ]);
    }

    private function resolveDeltas(string $movementType, float $quantity): array
    {
        return match ($movementType) {
            self::TYPE_OPENING, self::TYPE_IN, self::TYPE_ADJUSTMENT_PLUS, self::TYPE_TRANSFER_IN => [$quantity, 0],
            self::TYPE_OUT, self::TYPE_ADJUSTMENT_MINUS, self::TYPE_TRANSFER_OUT => [-$quantity, 0],
            self::TYPE_DAMAGED_IN => [-$quantity, $quantity],
            self::TYPE_DAMAGED_OUT => [0, -$quantity],
            self::TYPE_RECOVER_DAMAGED => [$quantity, -$quantity],
            default => throw new \InvalidArgumentException('Jenis mutasi stok tidak dikenali.'),
        };
    }

    private function generateTransferCode(Product $product): string
    {
        return 'TRF-' . $product->id . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
    }
}

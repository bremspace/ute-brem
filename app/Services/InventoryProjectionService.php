<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Mesin proyeksi stok & inventory control (WMS).
 *
 * Rumus inti:
 *   ADU            = rata-rata item keluar per hari (terjual + dipakai servis)
 *   Safety Stock   = ADU * 3
 *   ROP            = (ADU * lead_time_days) + Safety Stock
 *   lead_time_days = estimasi otomatis dari rata-rata durasi PO (belajar dari riwayat)
 *   Need Restock   = TotalQty (product_stocks) <= ROP
 *   Qty to Order   = maxStock - TotalQty
 */
class InventoryProjectionService
{
    public const ABC_A = 'A';
    public const ABC_B = 'B';
    public const ABC_C = 'C';

    /** Jendela perhitungan ADU (hari). */
    protected const USAGE_DAYS = 30;

    /** Faktor safety (hari cadangan). */
    protected const SAFETY_FACTOR = 3;

    /**
     * Hitung ADU sebuah produk dalam N hari terakhir dari stock movement keluar.
     */
    public function computeAdu(int $productId, ?int $days = null, ?Carbon $to = null): float
    {
        $days = $days ?? static::USAGE_DAYS;
        $to = $to ?? Carbon::now();
        $from = (clone $to)->subDays($days);

        $out = (float) StockMovement::where('product_id', $productId)
            ->where('movement_type', StockLedgerService::TYPE_OUT)
            ->whereBetween('movement_at', [$from, $to])
            ->sum('quantity');

        return $days > 0 ? round($out / $days, 4) : 0.0;
    }

    /**
     * Estimasi lead time otomatis dari riwayat PO.
     */
    public function estimatedLeadTime(Product $product): int
    {
        $receivedPos = PurchaseOrder::where('product_id', $product->id)
            ->whereNotNull('received_at')
            ->get();

        if ($receivedPos->isNotEmpty()) {
            $totalDays = $receivedPos->sum(function ($po) {
                return $po->ordered_at->diffInDays($po->received_at);
            });
            $avgLead = (int) round($totalDays / $receivedPos->count());
            $avgLead = max(1, $avgLead);

            $product->forceFill(['lead_time_days' => $avgLead])->save();

            return $avgLead;
        }

        return max(1, (int) $product->lead_time_days);
    }

    /**
     * Hitung safety stock & ROP berdasar ADU produk.
     */
    public function recalculateProjection(Product $product, ?float $adu = null): array
    {
        $adu = $adu ?? $this->computeAdu($product->id);
        $lead = $this->estimatedLeadTime($product);
        $safetyStock = round($adu * static::SAFETY_FACTOR, 4);
        $rop = round(($adu * $lead) + $safetyStock, 4);

        $product->forceFill([
            'adu' => $adu,
            'safety_stock' => $safetyStock,
            'rop' => $rop,
            'last_projection_at' => Carbon::now(),
        ])->save();

        return ['adu' => $adu, 'safety_stock' => $safetyStock, 'rop' => $rop, 'lead_time_days' => $lead];
    }

    /**
     * Total stok aktif sebuah produk (jumlah semua lokasi di product_stocks).
     */
    public function currentStock(Product $product): float
    {
        return (float) ProductStock::where('product_id', $product->id)->sum('quantity');
    }

    /**
     * Jumlah yang harus dipesan: maxStock - stok saat ini (min 0).
     */
    public function quantityToOrder(Product $product, ?float $current = null): float
    {
        $current = $current ?? $this->currentStock($product);
        $max = (float) $product->stock_max;
        return $max > 0 ? max(0, $max - $current) : 0;
    }

    /**
     * Apakah produk perlu restock (stok <= ROP).
     */
    public function isCritical(Product $product, ?float $current = null, ?float $rop = null): bool
    {
        $current = $current ?? $this->currentStock($product);
        $rop = $rop ?? (float) ($product->rop ?: $product->stock_min);
        return $current <= $rop;
    }

    /**
     * Analisis ABC — grup berdasar volume pemakaian (A fast, B medium, C slow).
     * Pareto klasik: A = 70% nilai kumulatif, B = 95%, C = sisanya.
     */
    public function assignAbcClasses(?\Closure $progress = null): int
    {
        $products = Product::where('is_active', true)->withCount('stocks')->get();
        $usage = [];

        foreach ($products as $index => $p) {
            $usage[$p->id] = $this->computeAdu($p->id);
            if ($progress) {
                $progress($index + 1, $products->count());
            }
        }

        if (empty($usage)) {
            return 0;
        }

        arsort($usage);
        $total = array_sum($usage);
        $classes = [];
        $cumulative = 0.0;
        foreach ($usage as $productId => $value) {
            $cumulative += $value;
            $share = $total > 0 ? ($cumulative / $total) : 0;
            $class = $share <= 0.70 ? static::ABC_A : ($share <= 0.95 ? static::ABC_B : static::ABC_C);
            $classes[$productId] = $class;
        }

        $ids = array_keys($classes);
        foreach (array_chunk($ids, 200) as $chunk) {
            Product::whereIn('id', $chunk)->get()->each(function ($p) use ($classes) {
                $p->forceFill(['abc_class' => $classes[$p->id] ?? static::ABC_C])->save();
            });
        }

        return count($usage);
    }

    /**
     * Daftar produk kritis (perlu restock), dikelompokkan per supplier primary.
     *
     * @return array{all: Collection, by_supplier: Collection, total: int}
     */
    public function getCriticalItems(): array
    {
        $products = Product::where('is_active', true)
            ->with('suppliers')
            ->get();

        $critical = $products->filter(function ($p) {
            return $this->isCritical($p);
        })->values();

        $bySupplier = $critical->groupBy(function ($p) {
            $primary = $p->suppliers->first();
            return $primary ? $primary->id : 'no_supplier';
        });

        return [
            'all' => $critical,
            'by_supplier' => $bySupplier,
            'total' => $critical->count(),
        ];
    }
}

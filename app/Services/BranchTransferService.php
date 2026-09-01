<?php

namespace App\Services;

use App\Models\BranchTransfer;
use App\Models\BranchTransferItem;
use App\Models\LocationRack;
use Illuminate\Support\Facades\DB;

class BranchTransferService
{
    public function __construct(
        private readonly StockLedgerService $stockLedgerService
    ) {
    }

    public function generateTransferCode(): string
    {
        $today = now()->format('Ymd');
        $count = BranchTransfer::whereDate('created_at', now()->toDateString())->count();
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "TRF-{$today}-{$sequence}";
    }

    /**
     * Ship the branch transfer (Transition: draft -> in_transit)
     */
    public function ship(BranchTransfer $transfer): void
    {
        if ($transfer->status !== 'draft') {
            throw new \RuntimeException('Hanya transfer berstatus Draft yang dapat dikirim.');
        }

        $transfer->load('items.product');

        DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                // Check if source stock is sufficient
                $sourceStock = $item->product->stocks()
                    ->where('location_id', $transfer->source_location_id)
                    ->first();

                $availableQuantity = $sourceStock ? (float) $sourceStock->quantity : 0.0;
                if ($availableQuantity < (float) $item->quantity_sent) {
                    throw new \RuntimeException(
                        "Stok produk '{$item->product->name}' di lokasi asal tidak mencukupi. " .
                        "Tersedia: {$availableQuantity}, Diminta: {$item->quantity_sent}"
                    );
                }

                // Deduct stock from source location
                $sourceRack = $item->source_location_rack_id 
                    ? LocationRack::find($item->source_location_rack_id) 
                    : null;

                $this->stockLedgerService->applyMovement(
                    $item->product,
                    $transfer->sourceLocation,
                    StockLedgerService::TYPE_TRANSFER_OUT,
                    (float) $item->quantity_sent,
                    "Kirim cabang: " . ($transfer->notes ?? ''),
                    now(),
                    'branch_transfer_out',
                    $transfer->transfer_code,
                    $sourceRack
                );
            }

            $transfer->update([
                'status' => 'in_transit',
                'sent_at' => now(),
            ]);
        });
    }

    /**
     * Receive the branch transfer (Transition: in_transit -> completed)
     */
    public function receive(BranchTransfer $transfer, array $itemsData, int $receivedByUserId): void
    {
        if ($transfer->status !== 'in_transit') {
            throw new \RuntimeException('Hanya transfer berstatus Dalam Perjalanan (In Transit) yang dapat diterima.');
        }

        $transfer->load('items.product');

        DB::transaction(function () use ($transfer, $itemsData, $receivedByUserId) {
            foreach ($transfer->items as $item) {
                $data = $itemsData[$item->id] ?? [];
                $qtyReceived = isset($data['quantity_received']) ? (float) $data['quantity_received'] : (float) $item->quantity_sent;
                
                if ($qtyReceived < 0) {
                    throw new \InvalidArgumentException("Jumlah barang diterima untuk '{$item->product->name}' tidak boleh negatif.");
                }

                // Target rack resolution
                $targetRackId = $data['target_location_rack_id'] ?? $item->target_location_rack_id;
                $targetRack = $targetRackId ? LocationRack::find($targetRackId) : null;

                // Update item's received quantity and target rack
                $item->update([
                    'quantity_received' => $qtyReceived,
                    'target_location_rack_id' => $targetRack?->id,
                ]);

                // Add stock to target location
                $qtyToTransferIn = (float) $item->quantity_sent;
                if ($qtyToTransferIn > 0) {
                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $transfer->targetLocation,
                        StockLedgerService::TYPE_TRANSFER_IN,
                        $qtyToTransferIn,
                        "Terima cabang: " . ($transfer->notes ?? ''),
                        now(),
                        'branch_transfer_in',
                        $transfer->transfer_code,
                        $targetRack
                    );
                }

                // Handle discrepancy (losses/damages in transit)
                $discrepancy = (float) $item->quantity_sent - $qtyReceived;
                if ($discrepancy > 0) {
                    // Log discrepancy as damaged/lost stock at target location
                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $transfer->targetLocation,
                        StockLedgerService::TYPE_DAMAGED_IN,
                        $discrepancy,
                        "Selisih transfer/Rusak di jalan: " . $transfer->transfer_code,
                        now(),
                        'branch_transfer_discrepancy',
                        $transfer->transfer_code,
                        $targetRack
                    );

                    // And immediately mark it as written off (damaged_out) so it doesn't inflate stock
                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $transfer->targetLocation,
                        StockLedgerService::TYPE_DAMAGED_OUT,
                        $discrepancy,
                        "Penghapusan selisih transfer/Rusak di jalan: " . $transfer->transfer_code,
                        now(),
                        'branch_transfer_discrepancy_writeoff',
                        $transfer->transfer_code,
                        $targetRack
                    );
                }
            }

            $transfer->update([
                'status' => 'completed',
                'received_at' => now(),
                'received_by' => $receivedByUserId,
            ]);
        });
    }

    /**
     * Cancel the branch transfer
     */
    public function cancel(BranchTransfer $transfer): void
    {
        if (in_array($transfer->status, ['completed', 'cancelled'])) {
            throw new \RuntimeException('Transfer yang sudah selesai atau dibatalkan tidak dapat diubah lagi.');
        }

        $transfer->load('items.product');

        DB::transaction(function () use ($transfer) {
            // If it was already in_transit, we must revert/return the stock to the source location
            if ($transfer->status === 'in_transit') {
                foreach ($transfer->items as $item) {
                    $sourceRack = $item->source_location_rack_id 
                        ? LocationRack::find($item->source_location_rack_id) 
                        : null;

                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $transfer->sourceLocation,
                        StockLedgerService::TYPE_ADJUSTMENT_PLUS,
                        (float) $item->quantity_sent,
                        "Batal kirim cabang (revert): " . $transfer->transfer_code,
                        now(),
                        'branch_transfer_cancel',
                        $transfer->transfer_code,
                        $sourceRack
                    );
                }
            }

            $transfer->update([
                'status' => 'cancelled',
            ]);
        });
    }
}

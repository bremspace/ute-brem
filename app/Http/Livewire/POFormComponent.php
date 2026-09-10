<?php

namespace App\Http\Livewire;

use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\StockLedgerService;
use App\Services\BackOfficeCashService;
use App\Services\AccountingPostingService;
use App\Services\InventoryProjectionService;
use App\Http\Controllers\PrinterSettingController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class POFormComponent extends Component
{
    public ?int $product_id = null;
    public ?int $supplier_id = null;
    public ?int $location_id = null;
    public float $quantity = 0.0;
    public ?float $unit_price = null;
    public string $ordered_at = '';
    public string $notes = '';
    public ?string $payment_method = 'cash';
    public float $paid_amount = 0.0;
    public ?int $credit_term_days = null;

    public string $productSearch = '';
    public ?Product $selectedProduct = null;

    public function mount(?int $productId = null, ?int $locationId = null): void
    {
        $this->ordered_at = now()->toDateString();

        if ($productId) {
            $this->selectProduct($productId, $locationId);
        }
    }

    public function selectProduct(int $productId, ?int $locationId = null): void
    {
        $product = Product::with(['productSuppliers.supplier', 'defaultLocation', 'stocks.location'])
            ->find($productId);

        if (! $product) {
            return;
        }

        $this->selectedProduct = $product;
        $this->product_id = $product->id;

        // Set default location
        $this->location_id = $locationId ?: $product->default_location_id;

        // Set primary supplier
        $primarySupplier = $product->productSuppliers->firstWhere('is_primary', true)
            ?: $product->productSuppliers->first();
        $this->supplier_id = $primarySupplier?->supplier_id;

        // Set suggested quantity based on stock projection
        $selectedStock = $this->location_id
            ? $product->stocks->firstWhere('location_id', $this->location_id)
            : null;

        $this->quantity = $selectedStock?->stock_min !== null
            ? max(1, (float) $selectedStock->stock_min - (float) $selectedStock->quantity)
            : max(1, (float) ($product->stock_min ?? 0) - (float) ($product->stock_global ?? 0));

        $this->unit_price = (float) $product->purchase_price;
    }

    public function searchProducts(): array
    {
        if (strlen($this->productSearch) < 2) {
            return [];
        }

        return Product::where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->productSearch}%")
                    ->orWhere('product_code', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->product_code,
                'stock_global' => $p->stock_global,
                'stock_min' => $p->stock_min,
                'purchase_price' => (float) $p->purchase_price,
            ])
            ->toArray();
    }

    public function getTotalPriceProperty(): float
    {
        $price = $this->unit_price ?? 0;
        return $price * $this->quantity;
    }

    public function submitPO()
    {
        $this->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|numeric|min:0.01',
            'ordered_at' => 'required|date',
            'payment_method' => 'required|in:cash,tempo',
        ]);

        $totalPrice = $this->totalPrice;

        DB::transaction(function () use ($totalPrice) {
            $poNumber = $this->generatePoNumber();

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'product_id' => $this->product_id,
                'supplier_id' => $this->supplier_id,
                'location_id' => $this->location_id,
                'quantity' => $this->quantity,
                'unit_price' => $this->unit_price ?? 0,
                'total_price' => $totalPrice,
                'payment_method' => $this->payment_method,
                'credit_term_days' => $this->payment_method === 'tempo' ? $this->credit_term_days : null,
                'paid_amount' => $this->payment_method === 'cash' ? $totalPrice : $this->paid_amount,
                'status' => 'received',
                'notes' => $this->notes ?: null,
                'ordered_at' => $this->ordered_at,
                'received_at' => $this->ordered_at,
                'created_by' => auth()->id(),
            ]);

            // Apply stock movement
            $product = Product::findOrFail($this->product_id);
            $location = Location::findOrFail($this->location_id);
            $supplier = Supplier::findOrFail($this->supplier_id);

            $stockLedgerService = app(StockLedgerService::class);
            $movement = $stockLedgerService->applyMovement(
                $product,
                $location,
                'in',
                $this->quantity,
                "PO {$poNumber} dari {$supplier->name}" . ($this->notes ? " - {$this->notes}" : ''),
                $this->ordered_at,
                'purchase_order',
                $poNumber
            );

            $po->update(['stock_movement_id' => $movement->id]);

            // Update buying price using Moving Average HPP
            $currentHpp = (float) ($product->purchase_price ?: 0);
            $currentStock = (float) ($product->stock_global ?: 0);
            $qty = $this->quantity;
            $unitPrice = $this->unit_price ?? 0;

            if ($currentStock + $qty > 0) {
                $newHpp = (($currentStock * $currentHpp) + ($qty * $unitPrice)) / ($currentStock + $qty);
                if ($newHpp > 0) {
                    $product->update(['purchase_price' => $newHpp]);
                }
            }

            // Record cash outflow
            $backOfficeCashService = app(BackOfficeCashService::class);
            $outflowAmount = $this->payment_method === 'cash' ? $totalPrice : $this->paid_amount;
            if ($outflowAmount > 0) {
                $backOfficeCashService->recordPOOutflow(
                    $outflowAmount,
                    $this->payment_method,
                    $poNumber,
                    $supplier,
                    "Pembelian PO {$poNumber}",
                    $this->ordered_at
                );
            }

            // Post double-entry journal
            $accountingPostingService = app(AccountingPostingService::class);
            $accountingPostingService->postPurchase($po, auth()->id());
        });

        session()->flash('success', "Purchase order berhasil dibuat dan stok masuk sudah dicatat.");
        return redirect()->route('purchase-orders.index');
    }

    private function generatePoNumber(): string
    {
        $prefix = 'PO';
        do {
            $number = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }

    public function render()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('livewire.purchase-orders.po-form-component', [
            'suppliers' => $suppliers,
            'locations' => $locations,
        ]);
    }
}

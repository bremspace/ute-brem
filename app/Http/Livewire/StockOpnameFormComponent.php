<?php

namespace App\Http\Livewire;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Location;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockOpnameFormComponent extends Component
{
    public ?StockOpname $opname = null;
    public bool $isEditing = false;

    // Form state
    public ?int $locationId = null;
    public string $opnameDate = '';
    public ?string $notes = null;

    // Items (product_id => ['product_code', 'product_name', 'system_qty', 'actual_qty'])
    public array $items = [];
    public array $locations = [];

    public function mount(?int $opnameId = null): void
    {
        $this->opnameDate = now()->toDateString();

        // Load all active locations
        $this->locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($opnameId) {
            $this->loadOpname($opnameId);
        } else {
            // Default to first location
            $this->locationId = $this->locations[0]?->id;
            if ($this->locationId) {
                $this->loadStockForLocation();
            }
        }
    }

    public function loadOpname(int $opnameId): void
    {
        $this->opname = StockOpname::with(['location', 'items.product'])->findOrFail($opnameId);
        $this->isEditing = true;
        $this->locationId = $this->opname->location_id;
        $this->opnameDate = $this->opname->opname_date->format('Y-m-d');
        $this->notes = $this->opname->notes;

        // Load items with actual quantities
        $this->items = [];
        foreach ($this->opname->items as $item) {
            $this->items[$item->product_id] = [
                'product_code' => $item->product?->product_code ?? '-',
                'product_name' => $item->product?->name ?? '-',
                'system_qty' => (float) $item->system_qty,
                'actual_qty' => (float) $item->actual_qty,
                'difference' => (float) $item->difference,
            ];
        }
    }

    public function loadStockForLocation(): void
    {
        if (! $this->locationId) {
            $this->items = [];
            return;
        }

        $stockData = ProductStock::with('product')
            ->where('location_id', $this->locationId)
            ->orderBy('product_id')
            ->get();

        $this->items = [];
        foreach ($stockData as $stock) {
            $product = $stock->product;
            $this->items[$stock->product_id] = [
                'product_code' => $product?->product_code ?? '-',
                'product_name' => $product?->name ?? '-',
                'system_qty' => (float) $stock->quantity,
                'actual_qty' => (float) $stock->quantity, // Default actual = system
                'difference' => 0.0,
            ];
        }
    }

    public function updatedLocationId(): void
    {
        if (! $this->isEditing) {
            $this->loadStockForLocation();
        }
    }

    public function updatedItems(array $items): void
    {
        // Livewire auto-updates the items array from the form inputs
        // The difference calculation is done in the view
    }

    public function updated($property): void
    {
        if (str_starts_with($property, 'items.')) {
            // Extract product_id and field from property name
            // e.g., "items.123.actual_qty" -> product_id=123, field=actual_qty
            $parts = explode('.', $property);
            if (count($parts) >= 3 && isset($this->items[$parts[1]])) {
                $productId = $parts[1];
                $field = $parts[2];
                if (isset($this->items[$productId]['system_qty']) && isset($this->items[$productId]['actual_qty'])) {
                    $this->items[$productId]['difference'] = 
                        (float) $this->items[$productId]['actual_qty'] - (float) $this->items[$productId]['system_qty'];
                }
            }
        }
    }

    public function store(): void
    {
        $validated = $this->validate([
            'locationId' => 'required|exists:locations,id',
            'opnameDate' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $items = $this->items;

        return DB::transaction(function () use ($validated) {
            $code = 'OPN-' . date('Ymd') . '-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ123456789'), 0, 4));
            $opname = StockOpname::create([
                'opname_code' => $code,
                'location_id' => $validated['locationId'],
                'opname_date' => $validated['opnameDate'],
                'status' => 'open',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $productId => $itemData) {
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $productId,
                    'system_qty' => $itemData['system_qty'],
                    'actual_qty' => $itemData['actual_qty'],
                    'difference' => (float) $itemData['actual_qty'] - (float) $itemData['system_qty'],
                ]);
            }

            session()->flash('success', 'Stock opname berhasil dibuat. Silakan periksa selisih lalu proses.');
            return redirect()->route('stock-opname.show', $opname);
        });
    }

    public function update(): void
    {
        if (! $this->opname) {
            return;
        }

        $validated = $this->validate([
            'locationId' => 'required|exists:locations,id',
            'opnameDate' => 'required|date',
            'notes' => 'nullable|string|max:255',
        ]);

        $items = $this->items;

        DB::transaction(function () use ($validated) {
            $this->opname->update([
                'location_id' => $validated['locationId'],
                'opname_date' => $validated['opnameDate'],
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $productId => $itemData) {
                StockOpnameItem::updateOrCreate(
                    [
                        'stock_opname_id' => $this->opname->id,
                        'product_id' => $productId,
                    ],
                    [
                        'system_qty' => $itemData['system_qty'],
                        'actual_qty' => $itemData['actual_qty'],
                        'difference' => (float) $itemData['actual_qty'] - (float) $itemData['system_qty'],
                    ]
                );
            }

            session()->flash('success', 'Stock opname berhasil diperbarui.');
        });
    }

    public function getTotalDiffValueProperty(): float
    {
        $total = 0.0;
        foreach ($this->items as $productId => $item) {
            $diff = (float) $item['difference'];
            // We need product purchase_price, but we don't have it here
            // For now, return 0 - we'll load it in the view
        }
        return $total;
    }

    public function getTotalDifferenceProperty(): int
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += (int) $item['difference'];
        }
        return $total;
    }

    public function getHasDifferenceProperty(): bool
    {
        foreach ($this->items as $item) {
            if (abs((float) $item['difference']) > 0.009) {
                return true;
            }
        }
        return false;
    }

    public function render()
    {
        return view('livewire.stock-opname.stock-opname-form-component');
    }
}
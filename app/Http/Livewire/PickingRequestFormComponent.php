<?php

namespace App\Http\Livewire;

use App\Models\PickingRequest;
use App\Models\PickingRequestItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PickingRequestFormComponent extends Component
{
    public ?PickingRequest $request = null;
    public bool $isEditing = false;

    // Form state
    public ?int $locationId = null;
    public ?int $technicianId = null;
    public ?string $notes = null;

    // Items
    public array $items = [];

    // Search
    public string $productSearch = '';
    public array $locations = [];
    public array $products = [];
    public array $stockMap = [];

    // For fulfill
    public bool $showFulfillModal = false;

    protected $rules = [
        'locationId' => 'required|exists:locations,id',
        'technicianId' => 'nullable|exists:users,id',
        'notes' => 'nullable|string|max:500',
    ];

    public function mount(?int $requestId = null): void
    {
        // Load locations
        $this->locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        // Load products
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'product_code', 'name', 'has_serial_number']);
        $stockData = ProductStock::whereIn('product_id', $products->pluck('id'))
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $this->products = $products->toArray();
        $this->stockMap = $stockData->toArray();

        // Default location to first
        if (empty($this->locationId) && ! empty($this->locations)) {
            $this->locationId = $this->locations[0]['id'];
        }

        if ($requestId) {
            $this->loadRequest($requestId);
        }
    }

    public function loadRequest(int $requestId): void
    {
        $this->request = PickingRequest::with(['items.product', 'technician', 'location'])->findOrFail($requestId);
        $this->isEditing = true;
        $this->locationId = $this->request->location_id;
        $this->technicianId = $this->request->technician_id;
        $this->notes = $this->request->notes;

        // Load items
        foreach ($this->request->items as $item) {
            $this->items[$item->product_id] = [
                'product_id' => $item->product_id,
                'product_code' => $item->product?->product_code ?? '-',
                'product_name' => $item->product?->name ?? '-',
                'qty' => (float) $item->qty_requested,
                'note' => $item->note,
                'status' => $item->status,
            ];
        }
    }

    public function addProduct($productId): void
    {
        if (isset($this->items[$productId])) {
            session()->flash('error', 'Produk sudah ditambahkan ke tabel.');
            return;
        }

        $product = Product::find($productId);
        if (! $product) {
            return;
        }

        $this->items[$productId] = [
            'product_id' => $productId,
            'product_code' => $product->product_code,
            'product_name' => $product->name,
            'qty' => 1,
            'note' => null,
            'status' => 'requested',
        ];

        $this->productSearch = '';
    }

    public function removeItem($productId): void
    {
        unset($this->items[$productId]);
    }

    public function updateItem($productId, $field, $value): void
    {
        if (isset($this->items[$productId])) {
            $this->items[$productId][$field] = $value;
        }
    }

    public function store()
    {
        $validated = $this->validate();

        if (empty($this->items)) {
            session()->flash('error', 'Tambahkan minimal satu produk.');
            return;
        }

        $code = 'PKG-' . date('Ymd') . '-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));

        $request = DB::transaction(function () use ($validated, $code) {
            $req = PickingRequest::create([
                'request_code' => $code,
                'technician_id' => $validated['technicianId'] ?? auth()->id(),
                'location_id' => $validated['locationId'],
                'status' => 'open',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $productId => $itemData) {
                PickingRequestItem::create([
                    'picking_request_id' => $req->id,
                    'product_id' => $productId,
                    'qty_requested' => $itemData['qty'],
                    'qty_picked' => 0,
                    'status' => 'requested',
                    'note' => $itemData['note'] ?? null,
                ]);
            }

            return $req;
        });

        session()->flash('success', 'Picking request berhasil dibuat.');
        return redirect()->route('picking-requests.show', $request);
    }

    public function getFilteredProductsProperty(): array
    {
        if (empty($this->productSearch)) {
            return $this->products;
        }

        $search = strtolower($this->productSearch);
        return array_filter($this->products, function ($product) use ($search) {
            return str_contains(strtolower($product['product_code']), $search) ||
                   str_contains(strtolower($product['name']), $search);
        });
    }

    public function render()
    {
        return view('livewire.picking-requests.picking-request-form-component', [
            'filteredProducts' => $this->filteredProducts,
            'stockMap' => $this->stockMap,
        ]);
    }
}
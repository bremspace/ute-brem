<?php

namespace App\Http\Livewire;

use App\Models\BackOfficeStockDocument;
use App\Models\Location;
use App\Models\LocationRack;
use App\Models\Product;
use App\Services\StockLedgerService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockDocumentsComponent extends Component
{
    protected StockLedgerService $stockLedgerService;

    public function boot(StockLedgerService $stockLedgerService): void
    {
        $this->stockLedgerService = $stockLedgerService;
    }

    public string $type = 'usage';
    public string $documentDate = '';
    public ?int $productId = null;
    public ?int $locationId = null;
    public ?int $locationRackId = null;
    public string $movementType = '';
    public float $quantity = 0;
    public string $description = '';

    public bool $showFormModal = false;
    public array $racks = [];

    public function mount(string $type = 'usage'): void
    {
        $this->type = in_array($type, ['correction', 'usage']) ? $type : 'usage';
        $this->documentDate = now()->toDateString();
        $this->movementType = $this->type === 'correction' ? 'adjustment_plus' : 'out';
    }

    public function updatedLocationId(): void
    {
        $this->racks = $this->locationId
            ? LocationRack::where('location_id', $this->locationId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray()
            : [];
    }

    public function openFormModal(): void
    {
        $this->resetForm();
        $this->documentDate = now()->toDateString();
        $this->showFormModal = true;
    }

    public function resetForm(): void
    {
        $this->productId = null;
        $this->locationId = null;
        $this->locationRackId = null;
        $this->movementType = $this->type === 'correction' ? 'adjustment_plus' : 'out';
        $this->quantity = 0;
        $this->description = '';
        $this->racks = [];
    }

    public function store(): void
    {
        $validated = $this->validate([
            'documentDate' => 'required|date',
            'productId' => 'required|exists:products,id',
            'locationId' => 'required|exists:locations,id',
            'locationRackId' => 'nullable|exists:location_racks,id',
            'movementType' => 'required|string',
            'quantity' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['productId']);
        $location = Location::findOrFail($validated['locationId']);
        $rack = $validated['locationRackId'] ? LocationRack::find($validated['locationRackId']) : null;

        DB::transaction(function () use ($validated, $product, $location, $rack) {
            $code = $this->generateCode($this->type === 'correction' ? 'KRS' : 'PMB', BackOfficeStockDocument::class, 'document_code');
            $movement = $this->stockLedgerService->applyMovement(
                $product,
                $location,
                $validated['movementType'],
                (float) $validated['quantity'],
                $validated['description'] ?? null,
                $validated['documentDate'],
                $this->type === 'correction' ? 'back_office_correction' : 'back_office_usage',
                $code,
                $rack
            );

            BackOfficeStockDocument::create([
                'document_code' => $code,
                'document_date' => $validated['documentDate'],
                'document_type' => $this->type,
                'product_id' => $product->id,
                'location_id' => $location->id,
                'location_rack_id' => $rack?->id,
                'movement_type' => $validated['movementType'],
                'quantity' => (float) $validated['quantity'],
                'description' => $validated['description'] ?? null,
                'stock_movement_id' => $movement->id,
                'created_by' => auth()->id(),
            ]);
        });

        session()->flash('success', ($this->type === 'correction' ? 'Koreksi stok' : 'Pemakaian barang') . ' berhasil dicatat.');
        $this->showFormModal = false;
    }

    private function generateCode(string $prefix, string $modelClass, string $column): string
    {
        do {
            $code = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while ($modelClass::where($column, $code)->exists());
        return $code;
    }

    public function getProductsProperty()
    {
        return Product::where('is_active', true)->orderBy('name')->limit(500)->get(['id', 'product_code', 'name']);
    }

    public function getLocationsProperty()
    {
        return Location::with(['racks' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getDocumentsProperty()
    {
        return BackOfficeStockDocument::with(['product', 'location', 'rack'])
            ->where('document_type', $this->type)
            ->latest('document_date')
            ->latest('id')
            ->limit(100)
            ->get();
    }

    public function render()
    {
        return view('livewire.back-office.stock-documents-component');
    }
}
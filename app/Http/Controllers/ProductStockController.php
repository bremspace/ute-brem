<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\LocationRack;
use App\Models\Product;
use App\Models\UserLog;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductStockController extends Controller
{
    public function __construct(
        private readonly StockLedgerService $stockLedgerService
    ) {
    }

    public function index(Product $product)
    {
        $product->load([
            'stocks.location',
            'stocks.rack',
            'stockMovements.location',
            'stockMovements.rack',
            'stockTransfers.sourceLocation',
            'stockTransfers.sourceRack',
            'stockTransfers.targetLocation',
            'stockTransfers.targetRack',
            'defaultLocation',
            'defaultRack',
        ]);

        $locations = Location::with(['racks' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('products.stocks.index', [
            'product' => $product,
            'locations' => $locations,
            'movementTypes' => $this->stockLedgerService->movementOptions(),
            'recentMovements' => $product->stockMovements->take(12),
            'recentTransfers' => $product->stockTransfers->take(12),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stocks' => 'nullable|array',
            'stocks.*.location_id' => 'required|exists:locations,id',
            'stocks.*.location_rack_id' => 'nullable|exists:location_racks,id',
            'stocks.*.stock_min' => 'nullable|numeric|min:0',
            'stocks.*.stock_max' => 'nullable|numeric|min:0',
            'stocks.*.notes' => 'nullable|string|max:500',
        ]);

        $product->load('stocks.location');
        $oldValues = $this->loggableStockState($product);

        $this->stockLedgerService->updateStockMetadata($product, $validated['stocks'] ?? []);

        $product->refresh()->load('stocks.location');

        UserLog::log(
            'UPDATE_PRODUCT_STOCK_META',
            "Updated stock metadata per location for product: {$product->name} ({$product->product_code})",
            null,
            $oldValues,
            $this->loggableStockState($product)
        );

        return redirect()
            ->route('products.stocks.index', $product)
            ->with('success', 'Batas minimum, maksimum, dan catatan lokasi berhasil diperbarui.');
    }

    public function storeMovement(Request $request, Product $product)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'location_rack_id' => 'nullable|exists:location_racks,id',
            'movement_type' => ['required', Rule::in(array_keys($this->stockLedgerService->movementOptions()))],
            'movement_quantity' => 'required|numeric|min:0.01',
            'movement_at' => 'required|date',
            'movement_notes' => 'nullable|string|max:500',
        ]);

        $location = Location::findOrFail($validated['location_id']);
        $rack = $this->resolveRack($validated['location_rack_id'] ?? null, $location);
        $product->load('stocks.location');
        $oldValues = $this->loggableStockState($product);

        try {
            $movement = $this->stockLedgerService->applyMovement(
                $product,
                $location,
                $validated['movement_type'],
                (float) $validated['movement_quantity'],
                $validated['movement_notes'] ?? null,
                $validated['movement_at'],
                null,
                null,
                $rack
            );
        } catch (\Throwable $exception) {
            return redirect()
                ->route('products.stocks.index', $product)
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $product->refresh()->load('stocks.location');

        UserLog::log(
            'CREATE_STOCK_MOVEMENT',
            "Created stock movement {$movement->movement_type} for product: {$product->name} at {$location->name}",
            null,
            $oldValues,
            $this->loggableStockState($product)
        );

        return redirect()
            ->route('products.stocks.index', $product)
            ->with('success', 'Mutasi stok berhasil dicatat.');
    }

    public function storeTransfer(Request $request, Product $product)
    {
        $validated = $request->validate([
            'source_location_id' => 'required|exists:locations,id',
            'source_location_rack_id' => 'nullable|exists:location_racks,id',
            'target_location_id' => 'required|exists:locations,id|different:source_location_id',
            'target_location_rack_id' => 'nullable|exists:location_racks,id',
            'transfer_quantity' => 'required|numeric|min:0.01',
            'transferred_at' => 'required|date',
            'transfer_notes' => 'nullable|string|max:500',
        ]);

        $sourceLocation = Location::findOrFail($validated['source_location_id']);
        $targetLocation = Location::findOrFail($validated['target_location_id']);
        $sourceRack = $this->resolveRack($validated['source_location_rack_id'] ?? null, $sourceLocation);
        $targetRack = $this->resolveRack($validated['target_location_rack_id'] ?? null, $targetLocation);
        $product->load('stocks.location');
        $oldValues = $this->loggableStockState($product);

        try {
            $transfer = $this->stockLedgerService->transfer(
                $product,
                $sourceLocation,
                $targetLocation,
                (float) $validated['transfer_quantity'],
                $validated['transfer_notes'] ?? null,
                $validated['transferred_at'],
                $sourceRack,
                $targetRack
            );
        } catch (\Throwable $exception) {
            return redirect()
                ->route('products.stocks.index', $product)
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $product->refresh()->load('stocks.location');

        UserLog::log(
            'CREATE_STOCK_TRANSFER',
            "Transferred stock for product: {$product->name} from {$sourceLocation->name} to {$targetLocation->name}",
            null,
            $oldValues,
            $this->loggableStockState($product)
        );

        return redirect()
            ->route('products.stocks.index', $product)
            ->with('success', 'Transfer stok berhasil dicatat.');
    }

    private function loggableStockState(Product $product): array
    {
        return [
            'stock_global' => $product->stock_global,
            'damaged_stock' => $product->damaged_stock,
            'locations' => $product->stocks->map(function ($stock) {
                return [
                    'location' => $stock->location?->name,
                    'rack' => $stock->rack?->name,
                    'quantity' => $stock->quantity,
                    'damaged_quantity' => $stock->damaged_quantity,
                    'stock_min' => $stock->stock_min,
                    'stock_max' => $stock->stock_max,
                ];
            })->values()->all(),
        ];
    }

    private function resolveRack(?int $rackId, Location $location): ?LocationRack
    {
        if (! $rackId) {
            return null;
        }

        return LocationRack::where('location_id', $location->id)->findOrFail($rackId);
    }
}

<?php

namespace App\Http\Livewire;

use App\Models\PickingRequest;
use App\Models\PickingRequestItem;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PickingRequestIndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?int $selectedRequestId = null;
    public bool $showFulfillConfirm = false;
    public ?int $requestToFulfill = null;

    protected $queryString = ['search', 'statusFilter', 'perPage'];

    #[On('picking-request-created')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    #[On('request-fulfilled')]
    public function onRequestFulfilled(): void
    {
        session()->flash('success', 'Picking request berhasil diproses.');
        $this->closeDetailModal();
    }

    public function openDetailModal(int $requestId): void
    {
        $this->selectedRequestId = $requestId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRequestId = null;
    }

    public function confirmFulfill(int $requestId): void
    {
        $this->requestToFulfill = $requestId;
        $this->showFulfillConfirm = true;
    }

    public function cancelFulfill(): void
    {
        $this->showFulfillConfirm = false;
        $this->requestToFulfill = null;
    }

    public function executeFulfill(): void
    {
        if (! $this->requestToFulfill) {
            return;
        }

        $request = PickingRequest::with('items.product')->find($this->requestToFulfill);
        if (! $request || $request->status !== 'open') {
            session()->flash('error', 'Request ini tidak dapat diproses.');
            $this->cancelFulfill();
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                foreach ($request->items as $item) {
                    $product = $item->product;
                    $qty = (float) $item->qty_requested;
                    if (! $product || $qty <= 0) {
                        continue;
                    }

                    // Apply stock movement
                    $this->applyStockMovement(
                        $product,
                        $request->location,
                        'out',
                        $qty,
                        'Picking untuk servis ' . $request->request_code,
                        now(),
                        'picking_request',
                        $request->request_code,
                        $item->rack
                    );

                    // Serialized products -> mark as reserved
                    if ($product->has_serial_number) {
                        $available = \App\Models\ItemSerial::where('product_id', $product->id)
                            ->where('status', 'available')
                            ->limit($qty)
                            ->get();
                        foreach ($available as $serial) {
                            $serial->update([
                                'status' => 'reserved',
                                'reference_type' => 'picking_request',
                                'reference_code' => $request->request_code,
                            ]);
                        }
                    }

                    $item->update([
                        'qty_picked' => $qty,
                        'status' => 'reserved',
                    ]);
                }

                $request->update(['status' => 'fulfilled']);
            });

            session()->flash('success', 'Picking diproses: stok gudang terpotong & sparepart berstatus Reserved for Repair.');
            $this->cancelFulfill();
            $this->resetPage();
        } catch (\Throwable $exception) {
            session()->flash('error', 'Gagal memproses picking: ' . $exception->getMessage());
            $this->cancelFulfill();
        }
    }

    private function applyStockMovement($product, $location, string $type, float $qty, string $desc, $date, string $refType, string $refCode, $rack = null)
    {
        $movementType = match($type) {
            'out' => 'out',
            default => 'out',
        };

        // Directly call StockLedgerService applyMovement pattern
        $stock = \App\Models\ProductStock::firstOrCreate(
            ['product_id' => $product->id, 'location_id' => $location->id],
            ['quantity' => 0, 'damaged_quantity' => 0]
        );

        $stockBefore = (float) $stock->quantity;
        $stockAfter = $stockBefore - $qty;

        if ($stockAfter < 0) {
            throw new \RuntimeException("Stok produk {$product->name} di lokasi {$location->name} tidak mencukupi.");
        }

        $stock->update(['quantity' => $stockAfter]);

        \App\Models\StockMovement::create([
            'product_id' => $product->id,
            'location_id' => $location->id,
            'location_rack_id' => $rack?->id,
            'movement_type' => $movementType,
            'quantity' => $qty,
            'good_delta' => -$qty,
            'damaged_delta' => 0,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'damaged_before' => 0,
            'damaged_after' => 0,
            'reference_type' => $refType,
            'reference_code' => $refCode,
            'notes' => $desc,
            'movement_at' => $date,
            'created_by' => auth()->id(),
        ]);
    }

    public function confirmCancel(int $requestId): void
    {
        $request = PickingRequest::find($requestId);
        if (! $request) {
            session()->flash('error', 'Request tidak ditemukan.');
            return;
        }

        if ($request->status === 'fulfilled') {
            session()->flash('error', 'Request yang sudah diproses tidak bisa dibatalkan.');
            return;
        }

        $request->update(['status' => 'cancelled']);
        session()->flash('success', 'Picking request dibatalkan.');
    }

    public function confirmDelete(int $requestId): void
    {
        $request = PickingRequest::find($requestId);
        if (! $request) {
            session()->flash('error', 'Request tidak ditemukan.');
            return;
        }

        if ($request->status === 'fulfilled') {
            session()->flash('error', 'Request yang sudah diproses tidak bisa dihapus.');
            return;
        }

        $request->delete();
        session()->flash('success', 'Picking request dihapus.');
    }

    public function getRequestsProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = PickingRequest::with(['technician', 'location', 'items.product']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('request_code', 'like', "%{$this->search}%")
                    ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$this->search}%"));
            });
        }

        return $query->orderByDesc('id')->paginate($this->perPage);
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => PickingRequest::count(),
            'open' => PickingRequest::where('status', 'open')->count(),
            'fulfilled' => PickingRequest::where('status', 'fulfilled')->count(),
            'cancelled' => PickingRequest::where('status', 'cancelled')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.picking-requests.picking-request-index-component', [
            'requests' => $this->requests,
            'stats' => $this->stats,
        ]);
    }
}
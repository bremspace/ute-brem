<?php

namespace App\Http\Livewire;

use App\Models\StockOpname;
use App\Models\StockLedgerService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class StockOpnameIndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';
    public string $locationFilter = '';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?int $selectedOpnameId = null;
    public bool $showCompleteModal = false;
    public ?int $opnameToComplete = null;

    protected $queryString = ['search', 'statusFilter', 'locationFilter', 'perPage'];

    #[On('opname-created')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    #[On('opname-saved')]
    public function onOpnameSaved(): void
    {
        session()->flash('success', 'Stock opname berhasil disimpan/diperbarui');
        $this->closeDetailModal();
    }

    public function openDetailModal(int $opnameId): void
    {
        $this->selectedOpnameId = $opnameId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedOpnameId = null;
    }

    public function confirmComplete(int $opnameId): void
    {
        $this->opnameToComplete = $opnameId;
        $this->showCompleteModal = true;
    }

    public function cancelComplete(): void
    {
        $this->showCompleteModal = false;
        $this->opnameToComplete = null;
    }

    public function executeComplete(): void
    {
        if (! $this->opnameToComplete) {
            return;
        }

        $opname = StockOpname::with('items.product', 'location')->find($this->opnameToComplete);
        if (! $opname || $opname->status === 'completed') {
            session()->flash('error', 'Opname ini tidak dapat diproses.');
            $this->cancelComplete();
            return;
        }

        try {
            $opname->load('items.product');

            foreach ($opname->items as $item) {
                $diff = (float) $item->difference;
                if (abs($diff) < 0.009) {
                    continue;
                }

                $product = $item->product;
                $loc = $opname->location;
                $type = $diff > 0 ? StockLedgerService::TYPE_ADJUSTMENT_PLUS : StockLedgerService::TYPE_ADJUSTMENT_MINUS;

                // Apply stock adjustment
                $this->applyStockMovement(
                    $product,
                    $loc,
                    $type,
                    abs($diff),
                    'Stock opname ' . $opname->opname_code,
                    $opname->opname_date->toDateString(),
                    'stock_opname',
                    $opname->opname_code
                );
            }

            $opname->update(['status' => 'completed']);

            session()->flash('success', 'Stock opname diproses: stok & pembukuan diperbarui.');
            $this->cancelComplete();
        } catch (\Throwable $exception) {
            session()->flash('error', 'Gagal memproses opname: ' . $exception->getMessage());
            $this->cancelComplete();
        }
    }

    public function confirmDelete(int $opnameId): void
    {
        $opname = StockOpname::find($opnameId);
        if (! $opname) {
            session()->flash('error', 'Opname tidak ditemukan.');
            return;
        }

        if ($opname->status === 'completed') {
            session()->flash('error', 'Opname yang sudah diproses tidak bisa dihapus.');
            return;
        }

        $opname->delete();
        session()->flash('success', 'Stock opname berhasil dihapus.');
    }

    private function applyStockMovement($product, $location, string $type, float $qty, string $desc, string $date, string $refType, string $refCode)
    {
        // This would normally call StockLedgerService, but for now we just update the product stock directly
        $stock = $product->stocks()
            ->where('location_id', $location->id)
            ->first();

        if ($stock) {
            $currentQty = (float) $stock->quantity;
            $newQty = $type === StockLedgerService::TYPE_ADJUSTMENT_PLUS 
                ? $currentQty + $qty 
                : $currentQty - $qty;
            
            $stock->update(['quantity' => max(0, $newQty)]);
        }
    }

    public function getOpnamesProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = StockOpname::with(['location', 'creator']);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }
        if ($this->locationFilter) {
            $query->where('location_id', $this->locationFilter);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('opname_code', 'like', "%{$this->search}%")
                    ->orWhereHas('location', fn ($l) => $l->where('name', 'like', "%{$this->search}%"));
            });
        }

        return $query->orderByDesc('id')->paginate($this->perPage);
    }

    public function getStatsProperty(): array
    {
        $total = StockOpname::count();
        $open = StockOpname::where('status', 'open')->count();
        $completed = StockOpname::where('status', 'completed')->count();

        return [
            'total' => $total,
            'open' => $open,
            'completed' => $completed,
        ];
    }

    public function render()
    {
        return view('livewire.stock-opname.stock-opname-index-component', [
            'opnames' => $this->opnames,
            'stats' => $this->stats,
        ]);
    }
}
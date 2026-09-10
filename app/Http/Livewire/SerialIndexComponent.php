<?php

namespace App\Http\Livewire;

use App\Models\ItemSerial;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class SerialIndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public ?int $productFilter = null;
    public int $perPage = 30;

    protected $queryString = ['search', 'statusFilter', 'productFilter', 'perPage'];

    public function getSerialsProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = ItemSerial::with(['product', 'productStock', 'rack', 'rack.location']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('serial_number', 'like', "%{$this->search}%")
                    ->orWhere('reference_code', 'like', "%{$this->search}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->productFilter) {
            $query->where('product_id', $this->productFilter);
        }

        return $query->latest()->paginate($this->perPage);
    }

    public function getStatsProperty(): array
    {
        return [
            'total' => ItemSerial::count(),
            'available' => ItemSerial::where('status', 'available')->count(),
            'reserved' => ItemSerial::where('status', 'reserved')->count(),
            'sold' => ItemSerial::where('status', 'sold')->count(),
        ];
    }

    public function getProductsProperty()
    {
        return Product::where('has_serial_number', true)
            ->orWhere('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'product_code']);
    }

    public function render()
    {
        return view('livewire.item-serials.serial-index-component', [
            'serials' => $this->serials,
            'stats' => $this->stats,
            'products' => $this->products,
        ]);
    }
}
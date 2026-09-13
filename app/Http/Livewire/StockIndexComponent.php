<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Location;
use App\Models\ProductStock;

class StockIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $filterLocation = '';
    public $filterLowStock = false;
    public $perPage = 15;
    protected $queryString = ['search', 'filterLocation', 'filterLowStock', 'perPage'];

    public function mount()
    {
        $this->locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterLocation() { $this->resetPage(); }
    public function updatedFilterLowStock() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    public function getProductsQuery()
    {
        $query = Product::query()
            ->with(['stocks.location', 'defaultLocation'])
            ->select(['id', 'product_code', 'name', 'stock_global', 'sale_unit', 'is_active']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('product_code', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterLocation) {
            $query->whereHas('stocks', fn($q) => $q->where('location_id', $this->filterLocation));
        }

        if ($this->filterLowStock) {
            $query->where('stock_global', '<', 10)
                  ->orWhereHas('stocks', fn($q) => $q->whereRaw('quantity < stock_min'));
        }

        return $query->orderBy('name');
    }

    public function getStockForLocation($productId, $locationId)
    {
        return ProductStock::where('product_id', $productId)
            ->where('location_id', $locationId)
            ->first();
    }

    public function render()
    {
        return view('livewire.stock-index', [
            'products' => $this->getProductsQuery()->paginate($this->perPage),
            'locations' => $this->locations ?? collect(),
        ]);
    }
}
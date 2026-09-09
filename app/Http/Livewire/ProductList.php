<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\ProductMaker;

class ProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCategory = '';
    public $filterBrand = '';
    public $filterMaker = '';
    public $filterStatus = '';
    public $perPage = 15;

    public $selectedProducts = [];
    public $selectAll = false;

    public $showDeleteModal = false;
    public $productIdToDelete = null;

    public $categories = [];
    public $brands = [];
    public $makers = [];

    public bool $isLoaded = false;
    public bool $isLoading = true;

    protected $queryString = ['search', 'filterCategory', 'filterBrand', 'filterMaker', 'filterStatus', 'perPage'];

    protected $listeners = ['productDeleted' => '$refresh'];

    public function mount()
    {
        $this->categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $this->brands = Brand::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $this->makers = ProductMaker::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $this->isLoaded = true;
        $this->isLoading = false;
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterCategory() { $this->resetPage(); }
    public function updatedFilterBrand() { $this->resetPage(); }
    public function updatedFilterMaker() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    public function updatingSelectAll($value)
    {
        $this->selectedProducts = $value ? $this->getProductsQuery()->pluck('id')->toArray() : [];
    }

    public function confirmDelete($id)
    {
        $this->productIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteProduct()
    {
        if ($this->productIdToDelete) {
            Product::findOrFail($this->productIdToDelete)->delete();
            session()->flash('success', 'Produk berhasil dihapus.');
            $this->dispatch('productDeleted');
            $this->resetPage();
        }
        $this->showDeleteModal = false;
        $this->productIdToDelete = null;
    }

    public function getProductsQuery()
    {
        $query = Product::query()
            ->with(['category:id,name', 'brand:id,name', 'maker:id,name'])
            ->select(['id', 'product_code', 'name', 'selling_price', 'stock_global', 'sale_unit', 'is_active', 'category_id', 'brand_id', 'product_maker_id', 'legacy_image_path']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('product_code', 'like', "%{$this->search}%")
                  ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        if ($this->filterBrand) {
            $query->where('brand_id', $this->filterBrand);
        }

        if ($this->filterMaker) {
            $query->where('product_maker_id', $this->filterMaker);
        }

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus === 'active');
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        return view('livewire.product-list', [
            'products' => $this->getProductsQuery()->paginate($this->perPage),
        ]);
    }
}
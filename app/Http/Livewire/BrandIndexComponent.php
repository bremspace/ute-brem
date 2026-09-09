<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Brand;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Builder;

class BrandIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $showDeleteModal = false;
    public $brandIdToDelete = null;

    protected $queryString = ['search', 'perPage'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->brandIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteBrand()
    {
        if ($this->brandIdToDelete) {
            $brand = Brand::findOrFail($this->brandIdToDelete);
            if ($brand->productTypes()->exists()) {
                session()->flash('error', 'Brand tidak dapat dihapus karena masih dipakai tipe produk.');
            } else {
                $brand->delete();
                UserLog::log('DELETE_BRAND', "Deleted brand: {$brand->name}", null, $brand->only(['name','slug','is_active']), null);
                session()->flash('success', 'Brand berhasil dihapus.');
            }
        }
        $this->showDeleteModal = false;
        $this->brandIdToDelete = null;
        $this->resetPage();
    }

    private function getBrandsQuery(): Builder
    {
        $query = Brand::query()
            ->withCount('productTypes')
            ->select(['id','name','slug','is_active','created_at']);
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('slug', 'like', "%{$this->search}%");
            });
        }
        return $query->orderBy('name');
    }

    public function render()
    {
        return view('livewire.brands.brand-index-component', [
            'brands' => $this->getBrandsQuery()->paginate($this->perPage),
        ]);
    }
}

<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Location;
use App\Models\Product;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Builder;

class LocationIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $showDeleteModal = false;
    public $locationIdToDelete = null;

    protected $queryString = ['search', 'perPage'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->locationIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteLocation()
    {
        if ($this->locationIdToDelete) {
            $location = Location::findOrFail($this->locationIdToDelete);
            if (Product::where('default_location_id', $location->id)->exists()) {
                session()->flash('error', 'Lokasi tidak dapat dihapus karena masih dipakai produk.');
            } elseif ($location->productStocks()->where(function ($q) {
                $q->where('quantity', '>', 0)->orWhere('damaged_quantity', '>', 0);
            })->exists()) {
                session()->flash('error', 'Lokasi tidak dapat dihapus karena masih memiliki saldo stok produk.');
            } elseif ($location->stockMovements()->exists() || $location->outgoingTransfers()->exists() || $location->incomingTransfers()->exists()) {
                session()->flash('error', 'Lokasi tidak dapat dihapus karena sudah memiliki histori mutasi atau transfer stok.');
            } else {
                UserLog::log('DELETE_LOCATION', "Deleted location: {$location->name}", null, $location->only(['name', 'code', 'is_active']));
                $location->delete();
                session()->flash('success', 'Lokasi berhasil dihapus.');
            }
        }
        $this->showDeleteModal = false;
        $this->locationIdToDelete = null;
        $this->resetPage();
    }

    private function getLocationsQuery(): Builder
    {
        $query = Location::query()
            ->withCount('racks')
            ->select(['id', 'name', 'code', 'is_active', 'created_at']);
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%");
            });
        }
        return $query->orderBy('name');
    }

    public function render()
    {
        return view('livewire.locations.location-index-component', [
            'locations' => $this->getLocationsQuery()->paginate($this->perPage),
        ]);
    }
}

<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Unit;
use App\Models\UserLog;
use Illuminate\Database\Eloquent\Builder;

class UnitIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $showDeleteModal = false;
    public $unitIdToDelete = null;

    protected $queryString = ['search', 'perPage'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->unitIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteUnit()
    {
        if ($this->unitIdToDelete) {
            $unit = Unit::findOrFail($this->unitIdToDelete);
            // Check product references similar to controller destroy
            $hasReferences = \App\Models\ProductUnit::where('unit_name', $unit->name)->exists() ||
                \App\Models\Product::where('unit', $unit->name)
                    ->orWhere('buy_unit', $unit->name)
                    ->orWhere('sale_unit', $unit->name)
                    ->exists();
            if ($hasReferences) {
                session()->flash('error', 'Satuan tidak dapat dihapus karena masih dipakai produk.');
            } else {
                $old = $unit->only(['name', 'code', 'is_active']);
                $unit->delete();
                UserLog::log('DELETE_UNIT', "Deleted unit: {$unit->name}", null, $old, null);
                session()->flash('success', 'Satuan berhasil dihapus.');
            }
        }
        $this->showDeleteModal = false;
        $this->unitIdToDelete = null;
        $this->resetPage();
    }

    private function getUnitsQuery(): Builder
    {
        $query = Unit::query()
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
        return view('livewire.units.unit-index-component', [
            'units' => $this->getUnitsQuery()->paginate($this->perPage),
        ]);
    }
}

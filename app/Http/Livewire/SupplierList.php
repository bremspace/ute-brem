<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;

class SupplierList extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;

    public $showDeleteModal = false;
    public $supplierIdToDelete = null;

    public bool $isLoaded = false;
    public bool $isLoading = true;

    protected $queryString = ['search', 'perPage'];

    protected $listeners = ['supplierDeleted' => '$refresh'];

    public function mount()
    {
        $this->isLoaded = true;
        $this->isLoading = false;
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    public function confirmDelete($id)
    {
        $this->supplierIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteSupplier()
    {
        if ($this->supplierIdToDelete) {
            Supplier::findOrFail($this->supplierIdToDelete)->delete();
            session()->flash('success', 'Supplier berhasil dihapus.');
            $this->dispatch('supplierDeleted');
            $this->resetPage();
        }
        $this->showDeleteModal = false;
        $this->supplierIdToDelete = null;
    }

    public function getSuppliersQuery()
    {
        $query = Supplier::query()->select(['id', 'code', 'name', 'phone', 'email', 'address', 'contact_person', 'is_active']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('code', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        return view('livewire.supplier-list', [
            'suppliers' => $this->getSuppliersQuery()->paginate($this->perPage),
        ]);
    }
}
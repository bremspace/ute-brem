<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

class CustomerList extends Component
{
    use WithPagination;

    public $search = '';
    public $filterMember = '';
    public $perPage = 15;

    public $showDeleteModal = false;
    public $customerIdToDelete = null;

    public bool $isLoaded = false;
    public bool $isLoading = true;

    protected $queryString = ['search', 'filterMember', 'perPage'];

    protected $listeners = ['customerDeleted' => '$refresh'];

    public function mount()
    {
        $this->isLoaded = true;
        $this->isLoading = false;
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterMember() { $this->resetPage(); }
    public function updatedPerPage() { $this->resetPage(); }

    public function confirmDelete($id)
    {
        $this->customerIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCustomer()
    {
        if ($this->customerIdToDelete) {
            Customer::findOrFail($this->customerIdToDelete)->delete();
            session()->flash('success', 'Pelanggan berhasil dihapus.');
            $this->dispatch('customerDeleted');
            $this->resetPage();
        }
        $this->showDeleteModal = false;
        $this->customerIdToDelete = null;
    }

    public function getCustomersQuery()
    {
        $query = Customer::query()->select(['id', 'member_code', 'name', 'phone', 'email', 'type', 'is_active', 'points_balance']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('member_code', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterMember !== '') {
            $query->where('type', $this->filterMember === 'true' ? 'member' : 'regular');
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        return view('livewire.customer-list', [
            'customers' => $this->getCustomersQuery()->paginate($this->perPage),
        ]);
    }
}
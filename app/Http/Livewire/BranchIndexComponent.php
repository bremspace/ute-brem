<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Branch;
use Livewire\WithFileUploads;

class BranchIndexComponent extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $perPage = 15;
    protected $queryString = ['search', 'perPage'];

    public $name = '';
    public $code = '';
    public $phone = '';
    public $address = '';
    public $is_main = false;
    public $is_active = true;

    public $branchId = null;
    public $action = 'create'; // 'create' or 'edit'

    public function mount()
    {
        $this->perPage = session('branches_per_page', 15);
    }

    public function updatedPerPage()
    {
        session(['branches_per_page' => $this->perPage]);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code' . ($this->branchId ? ',id,' . $this->branchId : ''),
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($this->is_main) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        if ($this->action === 'create') {
            Branch::create([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'phone' => $this->phone,
                'address' => $this->address,
                'is_main' => $this->is_main,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Cabang berhasil ditambahkan.');
        } else {
            $branch = Branch::find($this->branchId);
            $branch->update([
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'phone' => $this->phone,
                'address' => $this->address,
                'is_main' => $this->is_main,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Cabang berhasil diperbarui.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function edit(Branch $branch)
    {
        $this->branchId = $branch->id;
        $this->action = 'edit';
        $this->name = $branch->name;
        $this->code = $branch->code;
        $this->phone = $branch->phone;
        $this->address = $branch->address;
        $this->is_main = $branch->is_main;
        $this->is_active = $branch->is_active;
        $this->resetPage();
    }

    public function delete(Branch $branch)
    {
        if ($branch->is_main) {
            session()->flash('error', 'Cabang utama/pusat tidak dapat dihapus.');
            return;
        }

        if ($branch->users()->exists()) {
            session()->flash('error', 'Cabang tidak dapat dihapus karena masih memiliki user terdaftar.');
            return;
        }

        if ($branch->locations()->exists()) {
            session()->flash('error', 'Cabang tidak dapat dihapus karena masih memiliki lokasi penyimpanan.');
            return;
        }

        $branch->delete();
        session()->flash('success', 'Cabang berhasil dihapus.');
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset([
            'name',
            'code',
            'phone',
            'address',
            'is_main',
            'is_active',
            'branchId',
            'action',
        ]);
    }

    public function getBranchesQuery()
    {
        $query = Branch::query();

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
        return view('livewire.branch-index', [
            'branches' => $this->getBranchesQuery()->paginate($this->perPage),
        ]);
    }
}
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Supplier;
use Livewire\Forage;
use Illuminate\Support\Str;

class SupplierIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    protected $queryString = ['search', 'perPage'];

    public $name = '';
    public $code = '';
    public $slug = '';
    public $phone = '';
    public $email = '';
    public $contact_person = '';
    public $address = '';
    public $notes = '';
    public $is_active = true;

    public $supplierId = null;
    public $action = 'create'; // 'create' or 'edit'

    public function mount()
    {
        $this->perPage = session('suppliers_per_page', 15);
    }

    public function updatedPerPage()
    {
        session(['suppliers_per_page' => $this->perPage]);
    }

    public function resetFilters()
    {
        $this->clearForm();
        $this->resetPage();
    }

    protected function clearForm()
    {
        $this->reset([
            'name',
            'code',
            'slug',
            'phone',
            'email',
            'contact_person',
            'address',
            'notes',
            'is_active',
            'supplierId',
            'action',
        ]);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:suppliers,code' . ($this->supplierId ? ',id,' . $this->supplierId : ''),
            'slug' => 'nullable|string|max:255|unique:suppliers,slug' . ($this->supplierId ? ',id,' . $this->supplierId : ''),
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        if ($this->action === 'create') {
            $supplier = Supplier::create([
                'name' => $this->name,
                'code' => filled($this->code) ? Str::upper($this->code) : null,
                'slug' => $this->slug ?: Str::slug($this->name),
                'phone' => $this->phone,
                'email' => $this->email,
                'contact_person' => $this->contact_person,
                'address' => $this->address,
                'notes' => $this->notes,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Supplier berhasil ditambahkan.');
        } else {
            $supplier = Supplier::find($this->supplierId);
            $supplier->update([
                'name' => $this->name,
                'code' => filled($this->code) ? Str::upper($this->code) : null,
                'slug' => $this->slug ?: Str::slug($this->name),
                'phone' => $this->phone,
                'email' => $this->email,
                'contact_person' => $this->contact_person,
                'address' => $this->address,
                'notes' => $this->notes,
                'is_active' => $this->is_active,
            ]);
            session()->flash('success', 'Supplier berhasil diperbarui.');
        }

        $this->clearForm();
        $this->resetPage();
    }

    public function edit(Supplier $supplier)
    {
        $this->supplierId = $supplier->id;
        $this->action = 'edit';
        $this->name = $supplier->name;
        $this->code = $supplier->code;
        $this->slug = $supplier->slug;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->contact_person = $supplier->contact_person;
        $this->address = $supplier->address;
        $this->notes = $supplier->notes;
        $this->is_active = $supplier->is_active;
        $this->resetPage();
    }

    public function delete(Supplier $supplier)
    {
        if ($supplier->productSuppliers()->exists()) {
            session()->flash('error', 'Supplier tidak dapat dihapus karena masih terhubung ke produk.');
            return;
        }

        $supplier->delete();
        session()->flash('success', 'Supplier berhasil dihapus.');
        $this->resetPage();
    }

    public function getSuppliersQuery()
    {
        $query = Supplier::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orWhere('contact_person', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        return view('livewire.supplier-index', [
            'suppliers' => $this->getSuppliersQuery()->paginate($this->perPage),
        ]);
    }
}
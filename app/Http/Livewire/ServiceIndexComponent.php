<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class ServiceIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 10;
    protected $queryString = ['search', 'perPage'];

    public $service_code = '';
    public $name = '';
    public $slug = '';
    public $category = '';
    public $group = '';
    public $price_toko = '';
    public $price_partai = '';
    public $price_cabang = '';
    public $image = null;
    public $image_path = '';
    public $image_note = '';
    public $is_taxable = false;
    public $is_open_price = false;
    public $allow_discount_override = false;
    public $is_published = false;
    public $is_active = true;
    public $showImagePreview = false;

    public $serviceId = null;
    public $action = 'create'; // 'create' or 'edit'

    public function mount()
    {
        $this->perPage = session('services_per_page', 10);
    }

    public function updatedPerPage()
    {
        session(['services_per_page' => $this->perPage]);
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset([
            'service_code',
            'name',
            'slug',
            'category',
            'group',
            'price_toko',
            'price_partai',
            'price_cabang',
            'image',
            'image_path',
            'image_note',
            'is_taxable',
            'is_open_price',
            'allow_discount_override',
            'is_published',
            'is_active',
            'serviceId',
            'action',
        ]);
        $this->showImagePreview = false;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'service_code' => 'nullable|string|max:80|unique:services,service_code' . ($this->serviceId ? ',id,' . $this->serviceId : ''),
            'slug' => 'nullable|string|max:255|unique:services,slug' . ($this->serviceId ? ',id,' . $this->serviceId : ''),
            'category' => 'nullable|string|max:120',
            'group' => 'nullable|string|max:120',
            'price_toko' => 'required|numeric|min:0',
            'price_partai' => 'nullable|numeric|min:0',
            'price_cabang' => 'nullable|numeric|min:0',
            'image' => 'nullable|image|max:2048',
            'image_note' => 'nullable|string|max:255',
            'is_taxable' => 'nullable|boolean',
            'is_open_price' => 'nullable|boolean',
            'allow_discount_override' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $serviceCode = $this->service_code ?: $this->generateServiceCode();
        $slug = $this->slug ?: Str::slug($this->name);

        $data = [
            'service_code' => $serviceCode,
            'slug' => $slug,
            'category' => $this->category,
            'group' => $this->group,
            'price_toko' => $this->price_toko,
            'price_partai' => $this->price_partai,
            'price_cabang' => $this->price_cabang,
            'is_taxable' => $this->is_taxable,
            'is_open_price' => $this->is_open_price,
            'allow_discount_override' => $this->allow_discount_override,
            'is_published' => $this->is_published,
            'is_active' => $this->is_active,
        ];

        // Handle image upload
        if ($this->image) {
            $data['image_path'] = $this->image->store('services', 'public');

            // Clean up old image if editing
            if ($this->serviceId) {
                $oldService = \App\Models\Service::find($this->serviceId);
                if ($oldService && $oldService->image_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldService->image_path);
                }
            }

            if ($this->image_note) {
                $data['image_note'] = $this->image_note;
            }
        } elseif ($this->serviceId) {
            // Keep existing image if no new one uploaded
            $data['image_path'] = \App\Models\Service::find($this->serviceId)->image_path;
            $data['image_note'] = \App\Models\Service::find($this->serviceId)->image_note;
        }

        $service = \App\Models\Service::updateOrCreate(
            ['id' => $this->serviceId],
            $data
        );

        if ($this->action === 'create') {
            session()->flash('success', 'Jasa berhasil ditambahkan.');
        } else {
            session()->flash('success', 'Jasa berhasil diperbarui.');
        }

        $this->resetForm();
        $this->resetPage();
    }

    public function edit($serviceId)
    {
        $service = \App\Models\Service::find($serviceId);
        if (!$service) {
            return;
        }

        $this->serviceId = $service->id;
        $this->action = 'edit';
        $this->service_code = $service->service_code;
        $this->name = $service->name;
        $this->slug = $service->slug;
        $this->category = $service->category;
        $this->group = $service->group;
        $this->price_toko = $service->price_toko;
        $this->price_partai = $service->price_partai;
        $this->price_cabang = $service->price_cabang;
        $this->image_path = $service->image_path;
        $this->image_note = $service->image_note;
        $this->is_taxable = $service->is_taxable;
        $this->is_open_price = $service->is_open_price;
        $this->allow_discount_override = $service->allow_discount_override;
        $this->is_published = $service->is_published;
        $this->is_active = $service->is_active;
        $this->showImagePreview = $service->image_path ? true : false;
        $this->resetPage();
    }

    public function delete($serviceId)
    {
        $service = \App\Models\Service::find($serviceId);
        if (!$service) {
            return;
        }

        if ($service->transactionItems()->exists()) {
            session()->flash('error', 'Jasa tidak dapat dihapus karena sudah dipakai transaksi service.');
            return;
        }

        if ($service->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($service->image_path);
        }

        $service->delete();
        session()->flash('success', 'Jasa berhasil dihapus.');
        $this->resetPage();
    }

    public function generateServiceCode()
    {
        $code = 'JS-' . now()->format('ymdHis') . '-' . random_int(1000, 9999);
        while (\App\Models\Service::where('service_code', $code)->exists()) {
            $code = 'JS-' . now()->format('ymdHis') . '-' . random_int(1000, 9999);
        }
        return $code;
    }

    public function getServicesQuery()
    {
        $query = \App\Models\Service::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('service_code', 'like', "%{$this->search}%")
                    ->orWhere('category', 'like', "%{$this->search}%")
                    ->orWhere('group', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('name');
    }

    public function render()
    {
        return view('livewire.service-index', [
            'services' => $this->getServicesQuery()->paginate($this->perPage),
        ]);
    }
}
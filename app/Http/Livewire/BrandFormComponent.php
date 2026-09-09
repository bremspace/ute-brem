<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Brand;
use App\Models\UserLog;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandFormComponent extends Component
{
    public $brandId = null;
    public $name = '';
    public $slug = '';
    public $is_active = false;

    public function getRulesProperty(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($this->brandId)],
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function mount($brandId = null)
    {
        $this->brandId = $brandId;
        if ($brandId) {
            $brand = Brand::findOrFail($brandId);
            $this->name = $brand->name;
            $this->slug = $brand->slug;
            $this->is_active = (bool) $brand->is_active;
        }
    }

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    public function save()
    {
        $data = $this->validate();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? false;
        if ($this->brandId) {
            $brand = Brand::findOrFail($this->brandId);
            $old = $brand->only(['name', 'slug', 'is_active']);
            $brand->update([
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['slug'], $brand->id),
                'is_active' => $data['is_active'],
            ]);
            UserLog::log('UPDATE_BRAND', "Updated brand: {$brand->name}", null, $old, $brand->fresh()->only(['name','slug','is_active']));
        } else {
            $brand = Brand::create([
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['slug']),
                'is_active' => $data['is_active'],
            ]);
            UserLog::log('CREATE_BRAND', "Created brand: {$brand->name}", null, null, $brand->only(['name','slug','is_active']));
            session()->flash('success', 'Brand berhasil ditambahkan.');
        }
        return redirect()->route('brands.index');
    }

    private function generateUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug ?: Str::slug($this->name);
        $candidate = $base;
        $counter = 1;
        while (Brand::when($ignoreId, fn($q)=>$q->where('id','!=',$ignoreId))->where('slug', $candidate)->exists()) {
            $candidate = $base . '-' . $counter++;
        }
        return $candidate;
    }

    public function render()
    {
        return view('livewire.brands.brand-form-component');
    }
}

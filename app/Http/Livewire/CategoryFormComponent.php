<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\UserLog;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class CategoryFormComponent extends Component
{
    public $categoryId = null;
    public $name = '';
    public $slug = '';
    public $description = '';
    public $is_active = false;

    public function getRulesProperty(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($this->categoryId)],
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function mount($categoryId = null)
    {
        $this->categoryId = $categoryId;
        if ($categoryId) {
            $category = Category::findOrFail($categoryId);
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;
            $this->is_active = (bool) $category->is_active;
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
        if ($this->categoryId) {
            $category = Category::findOrFail($this->categoryId);
            $old = $category->only(['name', 'slug', 'description', 'is_active']);
            $category->update([
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['slug'], $category->id),
                'description' => $data['description'],
                'is_active' => $data['is_active'],
            ]);
            UserLog::log('UPDATE_CATEGORY', "Updated category: {$category->name}", null, $old, $category->fresh()->only(['name', 'slug', 'description', 'is_active']));
        } else {
            $category = Category::create([
                'name' => $data['name'],
                'slug' => $this->generateUniqueSlug($data['slug']),
                'description' => $data['description'],
                'is_active' => $data['is_active'],
            ]);
            UserLog::log('CREATE_CATEGORY', "Created category: {$category->name}", null, null, $category->only(['name', 'slug', 'is_active']));
            session()->flash('success', $this->categoryId ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
        return redirect()->route('categories.index');
        }
    }

    private function generateUniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug ?: Str::slug($this->name);
        $candidate = $base;
        $counter = 1;
        while (Category::withTrashed()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base . '-' . $counter++;
        }
        return $candidate;
    }

    public function render()
    {
        return view('livewire.categories.category-form-component');
    }
}

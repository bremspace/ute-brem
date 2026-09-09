<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\UserLog;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class CategoryIndexComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $showDeleteModal = false;
    public $categoryIdToDelete = null;

    protected $queryString = ['search', 'perPage'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->categoryIdToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteCategory()
    {
        if ($this->categoryIdToDelete) {
            $category = Category::findOrFail($this->categoryIdToDelete);
            if ($category->products()->exists()) {
                session()->flash('error', 'Kategori tidak dapat dihapus karena masih dipakai produk.');
            } else {
                $category->delete();
                UserLog::log('DELETE_CATEGORY', "Deleted category: {$category->name}", null, $category->only(['name', 'slug', 'is_active']), null);
                session()->flash('success', 'Kategori berhasil dihapus.');
            }
        }
        $this->showDeleteModal = false;
        $this->categoryIdToDelete = null;
        $this->resetPage();
    }

    private function getCategoriesQuery(): Builder
    {
        $query = Category::query()
            ->withCount('products')
            ->select(['id', 'name', 'slug', 'description', 'is_active', 'created_at']);
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
        return view('livewire.categories.category-index-component', [
            'categories' => $this->getCategoriesQuery()->paginate($this->perPage),
        ]);
    }
}

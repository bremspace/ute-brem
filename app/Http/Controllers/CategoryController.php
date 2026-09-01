<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index');
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log(
            'CREATE_CATEGORY',
            "Created category: {$category->name}",
            null,
            null,
            $category->only(['name', 'slug', 'is_active'])
        );

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $category->only(['name', 'slug', 'description', 'is_active']);
        $category->update([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $category->id),
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log(
            'UPDATE_CATEGORY',
            "Updated category: {$category->name}",
            null,
            $oldValues,
            $category->fresh()->only(['name', 'slug', 'description', 'is_active'])
        );

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Kategori tidak dapat dihapus karena masih dipakai produk.');
        }

        UserLog::log(
            'DELETE_CATEGORY',
            "Deleted category: {$category->name}",
            null,
            $category->only(['name', 'slug', 'description', 'is_active'])
        );

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    public function getData()
    {
        $categories = Category::query()
            ->withCount('products')
            ->select(['id', 'name', 'slug', 'description', 'is_active', 'created_at'])
            ->orderBy('name');

        return datatables()->of($categories)
            ->addColumn('products_count', fn ($category) => $category->products_count)
            ->addColumn('status_badge', function ($category) {
                $class = $category->is_active ? 'success' : 'secondary';
                $label = $category->is_active ? 'Aktif' : 'Nonaktif';
                return '<span class="badge bg-' . $class . '">' . $label . '</span>';
            })
            ->addColumn('action', function ($category) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                if (auth()->user()->hasPermission('master.categories.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('categories.edit', $category->id) . '">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>';
                }

                if (auth()->user()->hasPermission('master.categories.delete')) {
                    $actions .= '<form action="' . route('categories.destroy', $category->id) . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus kategori ini?\')">
                            <i class="bx bx-trash me-1"></i> Hapus
                        </button>
                    </form>';
                }

                $actions .= '</div></div>';

                return $actions;
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Category::withTrashed()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}

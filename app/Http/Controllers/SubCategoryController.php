<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    public function index()
    {
        return view('sub-categories.index');
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('sub-categories.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $subCategory = SubCategory::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('CREATE_SUB_CATEGORY', "Created sub category: {$subCategory->name}", null, null, $subCategory->only(['category_id', 'name', 'slug', 'is_active']));

        return redirect()->route('sub-categories.index')->with('success', 'Sub kategori berhasil ditambahkan.');
    }

    public function edit(SubCategory $subCategory)
    {
        $categories = Category::where('is_active', true)->orWhere('id', $subCategory->category_id)->orderBy('name')->get();
        return view('sub-categories.edit', compact('subCategory', 'categories'));
    }

    public function update(Request $request, SubCategory $subCategory)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('sub_categories', 'name')->ignore($subCategory->id)],
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $subCategory->only(['category_id', 'name', 'slug', 'is_active']);

        $subCategory->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $subCategory->id),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_SUB_CATEGORY', "Updated sub category: {$subCategory->name}", null, $oldValues, $subCategory->fresh()->only(['category_id', 'name', 'slug', 'is_active']));

        return redirect()->route('sub-categories.index')->with('success', 'Sub kategori berhasil diperbarui.');
    }

    public function destroy(SubCategory $subCategory)
    {
        if ($subCategory->category && $subCategory->category_id && \App\Models\Product::where('sub_category_id', $subCategory->id)->exists()) {
            return redirect()->route('sub-categories.index')->with('error', 'Sub kategori tidak dapat dihapus karena masih dipakai produk.');
        }

        UserLog::log('DELETE_SUB_CATEGORY', "Deleted sub category: {$subCategory->name}", null, $subCategory->only(['category_id', 'name', 'slug', 'is_active']));
        $subCategory->delete();

        return redirect()->route('sub-categories.index')->with('success', 'Sub kategori berhasil dihapus.');
    }

    public function getData()
    {
        $rows = SubCategory::with('category')->select(['id', 'category_id', 'name', 'slug', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('category_name', fn ($row) => $row->category?->name ?? '-')
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.sub_categories.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('sub-categories.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.sub_categories.delete')) {
                    $actions .= '<form action="' . route('sub-categories.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus sub kategori ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
                }
                return $actions . '</div></div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
        while (SubCategory::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        return $slug;
    }
}

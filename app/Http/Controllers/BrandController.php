<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index()
    {
        return view('brands.index');
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $brand = Brand::create([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('CREATE_BRAND', "Created brand: {$brand->name}", null, null, $brand->only(['name', 'slug', 'is_active']));

        return redirect()->route('brands.index')->with('success', 'Brand berhasil ditambahkan.');
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand->id)],
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $brand->only(['name', 'slug', 'is_active']);

        $brand->update([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $brand->id),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_BRAND', "Updated brand: {$brand->name}", null, $oldValues, $brand->fresh()->only(['name', 'slug', 'is_active']));

        return redirect()->route('brands.index')->with('success', 'Brand berhasil diperbarui.');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->productTypes()->exists() || Brand::where('id', $brand->id)->whereHas('productTypes.products')->exists()) {
            return redirect()->route('brands.index')->with('error', 'Brand tidak dapat dihapus karena masih dipakai.');
        }

        UserLog::log('DELETE_BRAND', "Deleted brand: {$brand->name}", null, $brand->only(['name', 'slug', 'is_active']));
        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand berhasil dihapus.');
    }

    public function getData()
    {
        $brands = Brand::query()
            ->select(['id', 'name', 'slug', 'is_active', 'created_at'])
            ->withCount('productTypes');

        return datatables()->of($brands)
            ->addColumn('product_types_count', fn ($brand) => $brand->product_types_count ?? 0)
            ->addColumn('status_badge', fn ($brand) => '<span class="badge bg-' . ($brand->is_active ? 'success' : 'secondary') . '">' . ($brand->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($brand) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.brands.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('brands.edit', $brand->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.brands.delete')) {
                    $actions .= '<form action="' . route('brands.destroy', $brand->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus brand ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
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

        while (Brand::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}

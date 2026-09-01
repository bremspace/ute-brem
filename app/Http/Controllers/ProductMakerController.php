<?php

namespace App\Http\Controllers;

use App\Models\ProductMaker;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductMakerController extends Controller
{
    public function index()
    {
        return view('product-makers.index');
    }

    public function create()
    {
        return view('product-makers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:product_makers,name',
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $maker = ProductMaker::create([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('CREATE_PRODUCT_MAKER', "Created product maker: {$maker->name}", null, null, $maker->only(['name', 'slug', 'is_active']));

        return redirect()->route('product-makers.index')->with('success', 'Merek berhasil ditambahkan.');
    }

    public function edit(ProductMaker $productMaker)
    {
        return view('product-makers.edit', compact('productMaker'));
    }

    public function update(Request $request, ProductMaker $productMaker)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_makers', 'name')->ignore($productMaker->id)],
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $productMaker->only(['name', 'slug', 'is_active']);

        $productMaker->update([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $productMaker->id),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_PRODUCT_MAKER', "Updated product maker: {$productMaker->name}", null, $oldValues, $productMaker->fresh()->only(['name', 'slug', 'is_active']));

        return redirect()->route('product-makers.index')->with('success', 'Merek berhasil diperbarui.');
    }

    public function destroy(ProductMaker $productMaker)
    {
        if ($productMaker->products()->exists()) {
            return redirect()->route('product-makers.index')->with('error', 'Merek tidak dapat dihapus karena masih dipakai produk.');
        }

        UserLog::log('DELETE_PRODUCT_MAKER', "Deleted product maker: {$productMaker->name}", null, $productMaker->only(['name', 'slug', 'is_active']));
        $productMaker->delete();

        return redirect()->route('product-makers.index')->with('success', 'Merek berhasil dihapus.');
    }

    public function getData()
    {
        $makers = ProductMaker::query()
            ->withCount('products')
            ->select(['id', 'name', 'slug', 'is_active', 'created_at']);

        return datatables()->of($makers)
            ->addColumn('products_count', fn ($maker) => $maker->products_count ?? 0)
            ->addColumn('status_badge', fn ($maker) => '<span class="badge bg-' . ($maker->is_active ? 'success' : 'secondary') . '">' . ($maker->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($maker) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';

                if (auth()->user()->hasPermission('master.products.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('product-makers.edit', $maker->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }

                if (auth()->user()->hasPermission('master.products.delete')) {
                    $actions .= '<form action="' . route('product-makers.destroy', $maker->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus merek ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
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

        while (ProductMaker::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}

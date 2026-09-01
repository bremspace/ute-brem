<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\ProductType;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductTypeController extends Controller
{
    public function index()
    {
        return view('product-types.index');
    }

    public function create()
    {
        $brands = Brand::where('is_active', true)->orderBy('name')->get();
        return view('product-types.create', compact('brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    $brandId = (int) $request->input('brand_id');
                    $normalized = $this->normalizeName((string) $value);

                    $exists = ProductType::query()
                        ->where('brand_id', $brandId)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($normalized)])
                        ->exists();

                    if ($exists) {
                        $fail('Nama tipe HP sudah ada untuk brand ini.');
                    }
                },
            ],
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['name'] = $this->normalizeName($validated['name']);

        $type = ProductType::create([
            'brand_id' => $validated['brand_id'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('CREATE_PRODUCT_TYPE', "Created product type: {$type->name}", null, null, $type->only(['brand_id', 'name', 'slug', 'is_active']));

        return redirect()->route('product-types.index')->with('success', 'Tipe HP berhasil ditambahkan.');
    }

    public function edit(ProductType $productType)
    {
        $brands = Brand::where('is_active', true)->orWhere('id', $productType->brand_id)->orderBy('name')->get();
        return view('product-types.edit', compact('productType', 'brands'));
    }

    public function update(Request $request, ProductType $productType)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $productType) {
                    $brandId = (int) $request->input('brand_id');
                    $normalized = $this->normalizeName((string) $value);

                    $exists = ProductType::query()
                        ->where('brand_id', $brandId)
                        ->where('id', '!=', $productType->id)
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($normalized)])
                        ->exists();

                    if ($exists) {
                        $fail('Nama tipe HP sudah ada untuk brand ini.');
                    }
                },
            ],
            'slug' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['name'] = $this->normalizeName($validated['name']);

        $oldValues = $productType->only(['brand_id', 'name', 'slug', 'is_active']);
        $productType->update([
            'brand_id' => $validated['brand_id'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $productType->id),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_PRODUCT_TYPE', "Updated product type: {$productType->name}", null, $oldValues, $productType->fresh()->only(['brand_id', 'name', 'slug', 'is_active']));

        return redirect()->route('product-types.index')->with('success', 'Tipe HP berhasil diperbarui.');
    }

    public function destroy(ProductType $productType)
    {
        if ($productType->products()->exists()) {
            return redirect()->route('product-types.index')->with('error', 'Tipe HP tidak dapat dihapus karena masih dipakai produk.');
        }

        UserLog::log('DELETE_PRODUCT_TYPE', "Deleted product type: {$productType->name}", null, $productType->only(['brand_id', 'name', 'slug', 'is_active']));
        $productType->delete();

        return redirect()->route('product-types.index')->with('success', 'Tipe HP berhasil dihapus.');
    }

    public function getData()
    {
        $rows = ProductType::with('brand')->select(['id', 'brand_id', 'name', 'slug', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('brand_name', fn ($row) => $row->brand?->name ?? '-')
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.product_types.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('product-types.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.product_types.delete')) {
                    $actions .= '<form action="' . route('product-types.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus tipe HP ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
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
        while (ProductType::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        return $slug;
    }

    private function normalizeName(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return $value;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        return view('services.index', [
            'servicesTableReady' => Schema::hasTable('services'),
        ]);
    }

    public function create()
    {
        return view('services.create', ['service' => null]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);
        $imagePath = $request->file('image')?->store('services', 'public');

        $service = Service::create(array_merge($validated, [
            'service_code' => $validated['service_code'] ?: $this->generateServiceCode(),
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name']),
            'image_path' => $imagePath,
            'is_taxable' => (bool) ($validated['is_taxable'] ?? false),
            'is_open_price' => (bool) ($validated['is_open_price'] ?? false),
            'allow_discount_override' => (bool) ($validated['allow_discount_override'] ?? false),
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'created_by' => auth()->id(),
        ]));

        UserLog::log('CREATE_SERVICE', "Created service: {$service->name}", null, null, $service->only(['service_code', 'name']));

        return redirect()->route('services.index')->with('success', 'Jasa berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $this->validatedPayload($request, $service);
        $oldValues = $service->only(['service_code', 'name', 'price_toko', 'is_open_price']);

        $imagePath = $service->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('services', 'public');
        }

        $service->update(array_merge($validated, [
            'service_code' => $validated['service_code'] ?: $service->service_code,
            'slug' => $this->generateUniqueSlug($validated['slug'] ?: $validated['name'], $service->id),
            'image_path' => $imagePath,
            'is_taxable' => (bool) ($validated['is_taxable'] ?? false),
            'is_open_price' => (bool) ($validated['is_open_price'] ?? false),
            'allow_discount_override' => (bool) ($validated['allow_discount_override'] ?? false),
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'updated_by' => auth()->id(),
        ]));

        UserLog::log('UPDATE_SERVICE', "Updated service: {$service->name}", null, $oldValues, $service->fresh()->only(['service_code', 'name', 'price_toko', 'is_open_price']));

        return redirect()->route('services.index')->with('success', 'Jasa berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        if ($service->transactionItems()->exists()) {
            return back()->with('error', 'Jasa tidak dapat dihapus karena sudah dipakai transaksi service.');
        }

        $service->delete();

        return redirect()->route('services.index')->with('success', 'Jasa berhasil dihapus.');
    }

    public function getData()
    {
        if (! Schema::hasTable('services')) {
            return response()->json(['draw' => (int) request('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        }

        $rows = Service::query()->select(['id', 'service_code', 'name', 'category', 'group', 'price_toko', 'is_open_price', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('price_label', fn (Service $row) => 'Rp ' . number_format((float) $row->price_toko, 0, ',', '.'))
            ->addColumn('open_price_badge', fn (Service $row) => '<span class="badge bg-label-' . ($row->is_open_price ? 'success' : 'secondary') . '">' . ($row->is_open_price ? 'Ya' : 'Tidak') . '</span>')
            ->addColumn('status_badge', fn (Service $row) => '<span class="badge bg-label-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function (Service $row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.products.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('services.edit', $row) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.products.delete')) {
                    $actions .= '<form action="' . route('services.destroy', $row) . '" method="POST">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus jasa ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
                }
                return $actions . '</div></div>';
            })
            ->rawColumns(['open_price_badge', 'status_badge', 'action'])
            ->make(true);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $rows = Service::query()
            ->where('is_active', true)
            ->when($q !== '', fn ($query) => $query->where(fn ($inner) => $inner
                ->where('name', 'like', "%{$q}%")
                ->orWhere('service_code', 'like', "%{$q}%")))
            ->orderBy('name')
            ->limit(25)
            ->get(['id', 'service_code', 'name', 'price_toko', 'is_open_price', 'allow_discount_override']);

        return response()->json([
            'results' => $rows->map(fn (Service $service) => [
                'id' => $service->id,
                'service_code' => $service->service_code,
                'name' => $service->name,
                'unit_price' => (float) $service->price_toko,
                'is_open_price' => (bool) $service->is_open_price,
                'allow_discount_override' => (bool) $service->allow_discount_override,
            ])->values()->all(),
        ]);
    }

    private function validatedPayload(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'service_code' => ['nullable', 'string', 'max:80', Rule::unique('services', 'service_code')->ignore($service?->id)],
            'name' => ['required', 'string', 'max:255', Rule::unique('services', 'name')->ignore($service?->id)],
            'slug' => 'nullable|string|max:255',
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
    }

    private function generateServiceCode(): string
    {
        do {
            $code = 'JS-' . now()->format('ymdHis') . '-' . random_int(1000, 9999);
        } while (Service::where('service_code', $code)->exists());

        return $code;
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $baseSlug;
        $counter = 1;

        while (Service::when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}

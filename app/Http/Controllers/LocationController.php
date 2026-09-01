<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\LocationRack;
use App\Models\Product;
use App\Models\UserLog;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index()
    {
        return view('locations.index');
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        return view('locations.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'code' => 'required|string|max:50|unique:locations,code',
            'branch_id' => 'nullable|exists:branches,id',
            'racks' => 'nullable|array',
            'racks.*' => 'nullable|string|max:100',
            'racks_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $branchId = $validated['branch_id'] ?? auth()->user()->branch_id;
        if (!$branchId) {
            $branchId = Branch::where('is_main', true)->value('id');
        }

        $location = Location::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'branch_id' => $branchId,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        $this->syncRacks($location, $this->extractRackNames($validated));

        UserLog::log('CREATE_LOCATION', "Created location: {$location->name}", null, null, $location->fresh('racks')->toArray());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lokasi berhasil ditambahkan.',
                'location' => $this->locationPayload($location->fresh('racks')),
            ], 201);
        }

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        $location->load('racks');
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('locations.edit', compact('location', 'branches'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('locations', 'name')->ignore($location->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('locations', 'code')->ignore($location->id)],
            'branch_id' => 'required|exists:branches,id',
            'racks' => 'nullable|array',
            'racks.*' => 'nullable|string|max:100',
            'racks_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $location->load('racks')->toArray();
        $location->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'branch_id' => $validated['branch_id'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);
        $this->syncRacks($location, $this->extractRackNames($validated));

        UserLog::log('UPDATE_LOCATION', "Updated location: {$location->name}", null, $oldValues, $location->fresh('racks')->toArray());
        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location)
    {
        if (Product::where('default_location_id', $location->id)->exists()) {
            return redirect()->route('locations.index')->with('error', 'Lokasi tidak dapat dihapus karena masih dipakai produk.');
        }

        if ($location->productStocks()->where(function ($query) {
            $query->where('quantity', '>', 0)->orWhere('damaged_quantity', '>', 0);
        })->exists()) {
            return redirect()->route('locations.index')->with('error', 'Lokasi tidak dapat dihapus karena masih memiliki saldo stok produk.');
        }

        if ($location->stockMovements()->exists() || $location->outgoingTransfers()->exists() || $location->incomingTransfers()->exists()) {
            return redirect()->route('locations.index')->with('error', 'Lokasi tidak dapat dihapus karena sudah memiliki histori mutasi atau transfer stok.');
        }

        if ($location->productStocks()->exists()) {
            return redirect()->route('locations.index')->with('error', 'Lokasi tidak dapat dihapus karena masih dipakai sebagai metadata stok produk.');
        }

        UserLog::log('DELETE_LOCATION', "Deleted location: {$location->name}", null, $location->only(['name', 'code', 'is_active']));
        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil dihapus.');
    }

    public function getData()
    {
        $rows = Location::query()->withCount('racks')->select(['id', 'name', 'code', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('racks_label', fn ($row) => $row->racks_count . ' rak')
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.locations.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('locations.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.locations.delete')) {
                    $actions .= '<form action="' . route('locations.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus lokasi ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
                }
                return $actions . '</div></div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    private function extractRackNames(array $validated): array
    {
        $items = $validated['racks'] ?? [];

        if (! empty($validated['racks_text'])) {
            $items = array_merge($items, preg_split('/\r\n|\r|\n|,/', $validated['racks_text']) ?: []);
        }

        return collect($items)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->all();
    }

    private function syncRacks(Location $location, array $rackNames): void
    {
        foreach ($rackNames as $rackName) {
            LocationRack::updateOrCreate(
                [
                    'location_id' => $location->id,
                    'name' => $rackName,
                ],
                [
                    'code' => strtoupper($location->code . '-' . str($rackName)->slug('-')),
                    'is_active' => true,
                ]
            );
        }

        if ($rackNames !== []) {
            LocationRack::where('location_id', $location->id)
                ->whereNotIn('name', $rackNames)
                ->update(['is_active' => false]);
        }
    }

    private function locationPayload(Location $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'code' => $location->code,
            'is_active' => $location->is_active,
            'racks' => $location->racks->map(fn ($rack) => [
                'id' => $rack->id,
                'name' => $rack->name,
                'location_id' => $rack->location_id,
            ])->values()->all(),
        ];
    }
}

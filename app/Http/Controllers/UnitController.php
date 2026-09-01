<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\UserLog;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index()
    {
        return view('units.index');
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:units,name',
            'code' => 'nullable|string|max:20|unique:units,code',
            'is_active' => 'nullable|boolean',
        ]);

        $unit = Unit::create([
            'name' => strtoupper($validated['name']),
            'code' => filled($validated['code'] ?? null) ? strtoupper($validated['code']) : substr(strtoupper($validated['name']), 0, 20),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        UserLog::log('CREATE_UNIT', "Created unit: {$unit->name}", null, null, $unit->only(['name', 'code', 'is_active']));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Satuan berhasil ditambahkan.',
                'unit' => $unit->only(['id', 'name', 'code', 'is_active']),
            ], 201);
        }

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('units', 'name')->ignore($unit->id)],
            'code' => ['nullable', 'string', 'max:20', Rule::unique('units', 'code')->ignore($unit->id)],
            'is_active' => 'nullable|boolean',
        ]);

        $oldValues = $unit->only(['name', 'code', 'is_active']);

        $unit->update([
            'name' => strtoupper($validated['name']),
            'code' => filled($validated['code'] ?? null) ? strtoupper($validated['code']) : substr(strtoupper($validated['name']), 0, 20),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_UNIT', "Updated unit: {$unit->name}", null, $oldValues, $unit->fresh()->only(['name', 'code', 'is_active']));

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    public function destroy(Unit $unit)
    {
        if (ProductUnit::where('unit_name', $unit->name)->exists() || Product::where('unit', $unit->name)->orWhere('buy_unit', $unit->name)->orWhere('sale_unit', $unit->name)->exists()) {
            return redirect()->route('units.index')->with('error', 'Satuan tidak dapat dihapus karena masih dipakai produk.');
        }

        UserLog::log('DELETE_UNIT', "Deleted unit: {$unit->name}", null, $unit->only(['name', 'code', 'is_active']));
        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }

    public function getData()
    {
        $rows = Unit::query()->select(['id', 'name', 'code', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.products.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('units.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.products.delete')) {
                    $actions .= '<form action="' . route('units.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus satuan ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
                }
                return $actions . '</div></div>';
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        abort_if(! auth()->user()->hasPermission('master.branches.view'), 403);
        return view('branches.index');
    }

    public function create()
    {
        abort_if(! auth()->user()->hasPermission('master.branches.create'), 403);
        return view('branches.create');
    }

    public function store(Request $request)
    {
        abort_if(! auth()->user()->hasPermission('master.branches.create'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'code' => 'required|string|max:50|unique:branches,code',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $isMain = (bool) ($validated['is_main'] ?? false);

        if ($isMain) {
            // Reset is_main on other branches
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch = Branch::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_main' => $isMain,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('CREATE_BRANCH', "Created branch: {$branch->name}", null, null, $branch->toArray());

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit(Branch $branch)
    {
        abort_if(! auth()->user()->hasPermission('master.branches.edit'), 403);
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        abort_if(! auth()->user()->hasPermission('master.branches.edit'), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('branches', 'name')->ignore($branch->id)],
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $isMain = (bool) ($validated['is_main'] ?? false);
        $oldValues = $branch->toArray();

        if ($isMain) {
            // Reset is_main on other branches
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'is_main' => $isMain,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        UserLog::log('UPDATE_BRANCH', "Updated branch: {$branch->name}", null, $oldValues, $branch->fresh()->toArray());

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        abort_if(! auth()->user()->hasPermission('master.branches.delete'), 403);

        if ($branch->is_main) {
            return redirect()->route('branches.index')->with('error', 'Cabang utama/pusat tidak dapat dihapus.');
        }

        if ($branch->users()->exists()) {
            return redirect()->route('branches.index')->with('error', 'Cabang tidak dapat dihapus karena masih memiliki user terdaftar.');
        }

        if ($branch->locations()->exists()) {
            return redirect()->route('branches.index')->with('error', 'Cabang tidak dapat dihapus karena masih memiliki lokasi penyimpanan.');
        }

        UserLog::log('DELETE_BRANCH', "Deleted branch: {$branch->name}", null, $branch->toArray());
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }

    public function getData()
    {
        abort_if(! auth()->user()->hasPermission('master.branches.view'), 403);

        $rows = Branch::query()->select(['id', 'name', 'code', 'is_main', 'is_active', 'created_at']);

        return datatables()->of($rows)
            ->addColumn('main_badge', fn ($row) => $row->is_main ? '<span class="badge bg-primary">Pusat</span>' : '<span class="badge bg-secondary">Cabang</span>')
            ->addColumn('status_badge', fn ($row) => '<span class="badge bg-' . ($row->is_active ? 'success' : 'secondary') . '">' . ($row->is_active ? 'Aktif' : 'Nonaktif') . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                if (auth()->user()->hasPermission('master.branches.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('branches.edit', $row->id) . '"><i class="bx bx-edit-alt me-1"></i>Edit</a>';
                }
                if (auth()->user()->hasPermission('master.branches.delete') && !$row->is_main) {
                    $actions .= '<form action="' . route('branches.destroy', $row->id) . '" method="POST" style="display:inline;">' . csrf_field() . method_field('DELETE') . '<button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus cabang ini?\')"><i class="bx bx-trash me-1"></i>Hapus</button></form>';
                }
                return $actions . '</div></div>';
            })
            ->rawColumns(['main_badge', 'status_badge', 'action'])
            ->make(true);
    }
}

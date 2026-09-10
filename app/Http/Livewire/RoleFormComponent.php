<?php

namespace App\Http\Livewire;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RoleFormComponent extends Component
{
    public ?Role $role = null;
    public bool $isEditing = false;

    public string $name = '';
    public string $displayName = '';
    public string $description = '';
    public array $selectedPermissions = [];

    public array $permissions = [];
    public array $permissionsByModule = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'displayName' => 'required|string|max:255',
        'description' => 'nullable|string',
        'selectedPermissions' => 'array',
    ];

    public function mount(?int $roleId = null): void
    {
        $allPermissions = Permission::all();
        $this->permissionsByModule = $allPermissions->groupBy('module')->toArray();
        $this->permissions = $allPermissions->toArray();

        if ($roleId) {
            $this->loadRole($roleId);
        }
    }

    public function loadRole(int $roleId): void
    {
        $this->role = Role::with('permissions')->findOrFail($roleId);
        $this->isEditing = true;
        $this->name = $this->role->name;
        $this->displayName = $this->role->display_name;
        $this->description = $this->role->description;
        $this->selectedPermissions = $this->role->permissions->pluck('id')->toArray();
    }

    public function togglePermission(int $permissionId): void
    {
        $key = array_search($permissionId, $this->selectedPermissions);
        if ($key !== false) {
            unset($this->selectedPermissions[$key]);
            $this->selectedPermissions = array_values($this->selectedPermissions);
        } else {
            $this->selectedPermissions[] = $permissionId;
        }
    }

    public function store()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255|unique:roles',
            'displayName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selectedPermissions' => 'array',
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'display_name' => $validated['displayName'],
                'description' => $validated['description'] ?? null,
                'guard_name' => 'web',
            ]);

            if (! empty($validated['selectedPermissions'])) {
                $role->permissions()->sync($validated['selectedPermissions']);
            }
        });

        session()->flash('success', 'Role berhasil ditambahkan.');
        return redirect()->route('roles.index');
    }

    public function update()
    {
        if (! $this->role) {
            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'displayName' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selectedPermissions' => 'array',
        ]);

        DB::transaction(function () use ($validated) {
            $this->role->update([
                'name' => $validated['name'],
                'display_name' => $validated['displayName'],
                'description' => $validated['description'] ?? null,
            ]);

            $this->role->permissions()->sync($validated['selectedPermissions'] ?? []);
        });

        session()->flash('success', 'Role berhasil diperbarui.');
        return redirect()->route('roles.index');
    }

    public function render()
    {
        return view('livewire.roles.role-form-component');
    }
}
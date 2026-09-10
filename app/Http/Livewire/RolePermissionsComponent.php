<?php

namespace App\Http\Livewire;

use App\Models\Role;
use App\Models\Permission;
use Livewire\Component;

class RolePermissionsComponent extends Component
{
    public ?Role $role = null;
    public array $selectedPermissionIds = [];
    public array $permissionsByModule = [];

    public function mount(int $roleId): void
    {
        $this->role = Role::with('permissions')->findOrFail($roleId);
        $allPermissions = Permission::all();
        $this->permissionsByModule = $allPermissions->groupBy('module')->toArray();
        $this->selectedPermissionIds = $this->role->permissions->pluck('id')->toArray();
    }

    public function togglePermission(int $permissionId): void
    {
        $key = array_search($permissionId, $this->selectedPermissionIds);
        if ($key !== false) {
            unset($this->selectedPermissionIds[$key]);
            $this->selectedPermissionIds = array_values($this->selectedPermissionIds);
        } else {
            $this->selectedPermissionIds[] = $permissionId;
        }
    }

    public function save(): void
    {
        $this->role->permissions()->sync($this->selectedPermissionIds);
        session()->flash('success', 'Permissions berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.roles.role-permissions-component');
    }
}
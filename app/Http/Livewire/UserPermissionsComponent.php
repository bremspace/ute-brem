<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserPermissionsComponent extends Component
{
    public ?User $user = null;
    public array $selectedPermissionIds = [];
    public array $permissionsByModule = [];

    protected $rules = [
        'selectedPermissionIds' => 'array',
    ];

    public function mount(int $userId): void
    {
        $this->user = User::with('roles.permissions')->findOrFail($userId);

        $allPermissions = Permission::all();
        $this->permissionsByModule = $allPermissions->groupBy('module')->toArray();

        $this->selectedPermissionIds = $this->user->roles->flatMap->permissions->pluck('id')->unique()->toArray();
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
        DB::transaction(function () {
            $this->user->roles()->detach();
            $dynamicRoleName = 'user_' . $this->user->id . '_permissions';
            $dynamicRole = Role::updateOrCreate(
                ['name' => $dynamicRoleName],
                ['display_name' => 'Custom Permissions for ' . $this->user->name]
            );
            $dynamicRole->permissions()->detach();
            $selectedPermissions = Permission::whereIn('id', $this->selectedPermissionIds)->get();
            foreach ($selectedPermissions as $permission) {
                $dynamicRole->assignPermission($permission);
            }
            $this->user->assignRole($dynamicRole);
        });

        session()->flash('success', 'User permissions berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.users.user-permissions-component');
    }
}
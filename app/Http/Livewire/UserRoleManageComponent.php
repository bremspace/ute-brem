<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class UserRoleManageComponent extends Component
{
    public ?User $user = null;
    public bool $isEditing = false;
    public array $selectedRoleIds = [];
    public array $availableRoles = [];
    public array $roleTemplates = [];

    protected $rules = [
        'selectedRoleIds' => 'array',
    ];

    public function mount(?int $userId = null): void
    {
        $this->availableRoles = Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'display_name'])
            ->toArray();

        $this->roleTemplates = Role::where('name', 'like', '%_template')
            ->orderBy('name')
            ->get(['id', 'name', 'display_name'])
            ->toArray();

        if ($userId) {
            $this->loadUser($userId);
        }
    }

    public function loadUser(int $userId): void
    {
        $this->user = User::with('roles')->findOrFail($userId);
        $this->isEditing = true;
        $this->selectedRoleIds = $this->user->roles->pluck('id')->toArray();
    }

    public function toggleRole(int $roleId): void
    {
        $key = array_search($roleId, $this->selectedRoleIds);
        if ($key !== false) {
            unset($this->selectedRoleIds[$key]);
            $this->selectedRoleIds = array_values($this->selectedRoleIds);
        } else {
            $this->selectedRoleIds[] = $roleId;
        }
    }

    public function assignRole(int $roleId): void
    {
        $role = Role::find($roleId);
        if ($role && $this->user) {
            $this->user->assignRole($role);
            $this->selectedRoleIds[] = $roleId;
            session()->flash('success', 'Role berhasil ditambahkan.');
        }
    }

    public function removeRole(int $roleId): void
    {
        $role = Role::find($roleId);
        if ($role && $this->user) {
            $this->user->removeRole($role);
            $this->selectedRoleIds = array_diff($this->selectedRoleIds, [$roleId]);
            session()->flash('success', 'Role berhasil dihapus.');
        }
    }

    public function applyTemplate(int $templateId): void
    {
        $template = Role::find($templateId);
        if ($template && $this->user) {
            $this->user->roles()->detach();
            $this->user->assignRole($template);
            $this->selectedRoleIds = [$templateId];
            session()->flash('success', "Template role '{$template->display_name}' diterapkan.");
        }
    }

    public function save(): void
    {
        if (! $this->user) {
            return;
        }

        DB::transaction(function () {
            $this->user->roles()->detach();
            foreach ($this->selectedRoleIds as $roleId) {
                $this->user->assignRole(Role::find($roleId));
            }
        });

        session()->flash('success', 'Role user berhasil diperbarui.');
        $this->isEditing = false;
    }

    public function render()
    {
        return view('livewire.users.user-role-manage-component');
    }
}
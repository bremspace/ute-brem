<?php

namespace App\Http\Livewire;

use App\Models\Role;
use App\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;

class RoleIndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?int $selectedRoleId = null;
    public bool $showDeleteConfirm = false;
    public ?int $roleToDelete = null;

    protected $queryString = ['search', 'perPage'];

    #[On('role-created')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    public function openDetailModal(int $roleId): void
    {
        $this->selectedRoleId = $roleId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedRoleId = null;
    }

    public function confirmDelete(int $roleId): void
    {
        $this->roleToDelete = $roleId;
        $this->showDeleteConfirm = true;
    }

    public function executeDelete(): void
    {
        $role = Role::find($this->roleToDelete);
        if ($role && $role->users()->count() === 0) {
            $role->delete();
            session()->flash('success', 'Role berhasil dihapus.');
        } else {
            session()->flash('error', 'Role tidak dapat dihapus karena masih digunakan oleh user.');
        }
        $this->cancelDelete();
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->roleToDelete = null;
    }

    public function getRolesProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Role::withCount(['users', 'permissions'])
            ->orderByDesc('id');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('display_name', 'like', "%{$this->search}%");
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.roles.role-index-component', [
            'roles' => $this->roles,
        ]);
    }
}
<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Role;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class UserIndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?int $selectedUserId = null;
    public bool $showRestoreModal = false;
    public ?int $userToRestore = null;
    public bool $showDeleteConfirm = false;
    public ?int $userToDelete = null;

    protected $queryString = ['search', 'statusFilter', 'perPage'];

    #[On('user-created')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    #[On('user-updated')]
    public function onUserUpdated(): void
    {
        session()->flash('success', 'User berhasil diperbarui.');
        $this->closeDetailModal();
    }

    public function openDetailModal(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedUserId = null;
    }

    public function confirmRestore(int $userId): void
    {
        $this->userToRestore = $userId;
        $this->showRestoreModal = true;
    }

    public function executeRestore(): void
    {
        $user = User::withTrashed()->find($this->userToRestore);
        if ($user) {
            $user->deleted_by = null;
            $user->restore();
            session()->flash('success', 'User berhasil dipulihkan.');
        }
        $this->cancelRestore();
        $this->resetPage();
    }

    public function cancelRestore(): void
    {
        $this->showRestoreModal = false;
        $this->userToRestore = null;
    }

    public function confirmDelete(int $userId): void
    {
        $this->userToDelete = $userId;
        $this->showDeleteConfirm = true;
    }

    public function executeDelete(): void
    {
        $user = User::find($this->userToDelete);
        if ($user) {
            $user->delete();
            session()->flash('success', 'User berhasil dihapus.');
        }
        $this->cancelDelete();
        $this->resetPage();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteConfirm = false;
        $this->userToDelete = null;
    }

    public function getUsersProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = User::with('roles', 'branch')
            ->orderByDesc('id');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('username', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        return $query->paginate($this->perPage);
    }

    public function getTrashedUsersProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        return User::onlyTrashed()
            ->with(['creator', 'deleter'])
            ->orderByDesc('deleted_at')
            ->paginate(15);
    }

    public function getBranchesProperty()
    {
        return Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getRolesProperty()
    {
        return Role::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'display_name']);
    }

    public function render()
    {
        return view('livewire.users.user-index-component', [
            'users' => $this->users,
            'trashedUsers' => $this->trashedUsers,
            'branches' => $this->branches,
            'roles' => $this->roles,
        ]);
    }
}
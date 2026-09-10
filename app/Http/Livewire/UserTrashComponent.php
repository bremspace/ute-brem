<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserTrashComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;

    public bool $showRestoreConfirm = false;
    public ?int $userToRestore = null;

    protected $queryString = ['search', 'perPage'];

    public function confirmRestore(int $userId): void
    {
        $this->userToRestore = $userId;
        $this->showRestoreConfirm = true;
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
        $this->showRestoreConfirm = false;
        $this->userToRestore = null;
    }

    public function confirmForceDelete(int $userId): void
    {
        $user = User::withTrashed()->find($userId);
        if ($user) {
            $user->forceDelete();
            session()->flash('success', 'User berhasil dihapus permanen.');
            $this->resetPage();
        }
    }

    public function getTrashedUsersProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = User::onlyTrashed()
            ->with(['creator', 'deleter'])
            ->orderByDesc('deleted_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('username', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%");
            });
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.users.user-trash-component', [
            'trashedUsers' => $this->trashedUsers,
        ]);
    }
}
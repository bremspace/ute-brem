<?php

namespace App\Http\Livewire;

use App\Models\UserLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserLogsComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $actionFilter = '';
    public int $perPage = 15;
    public ?int $selectedUserId = null;

    protected $queryString = ['search', 'actionFilter', 'perPage'];

    public function getLogsProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = UserLog::with(['user', 'targetUser'])
            ->orderByDesc('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->actionFilter !== '') {
            $query->where('action', $this->actionFilter);
        }

        return $query->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.users.user-logs-component', [
            'logs' => $this->logs,
        ]);
    }
}
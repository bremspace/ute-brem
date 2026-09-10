<?php

namespace App\Http\Livewire;

use App\Models\BranchTransfer;
use App\Models\Branch;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class TransferIndexComponent extends Component
{
    use WithPagination;

    public string $selectedStatus = 'all';
    public string $sourceBranchId = '';
    public string $targetBranchId = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public string $search = '';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?int $selectedTransferId = null;

    protected $queryString = ['selectedStatus', 'sourceBranchId', 'targetBranchId', 'dateFrom', 'dateTo', 'search', 'perPage'];

    public function mount(): void
    {
        $this->perPage = 15;
    }

    #[On('transfer-created')]
    public function refresh(): void
    {
        $this->resetPage();
    }

    #[On('transfer-saved')]
    public function onTransferSaved(): void
    {
        session()->flash('success', 'Transfer berhasil disimpan/diperbarui');
        $this->closeDetailModal();
    }

    public function openDetailModal(int $transferId): void
    {
        $this->selectedTransferId = $transferId;
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedTransferId = null;
    }

    public function getTransfersProperty(): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = BranchTransfer::with(['sourceBranch', 'targetBranch', 'sourceLocation', 'targetLocation', 'creator', 'recipient']);

        // Filters
        if ($this->selectedStatus !== 'all') {
            $query->where('status', $this->selectedStatus);
        }
        if ($this->sourceBranchId) {
            $query->where('source_branch_id', $this->sourceBranchId);
        }
        if ($this->targetBranchId) {
            $query->where('target_branch_id', $this->targetBranchId);
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
        }

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('transfer_code', 'like', "%{$this->search}%")
                    ->orWhereHas('sourceBranch', fn ($b) => $b->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('targetBranch', fn ($b) => $b->where('name', 'like', "%{$this->search}%"));
            });
        }

        return $query->latest()->paginate($this->perPage);
    }
        }

        return $query->latest()->paginate($this->perPage);
    }

    public function getBranchesProperty(): \Illuminate\Support\Collection
    {
        return Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getFilteredBranchesProperty(): \Illuminate\Support\Collection
    {
        return $this->branches;
    }

    public function getStatsProperty(): array
    {
        $query = BranchTransfer::query();

        if ($this->sourceBranchId) {
            $query->where('source_branch_id', $this->sourceBranchId);
        }
        if ($this->targetBranchId) {
            $query->where('target_branch_id', $this->targetBranchId);
        }
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
        }
        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
        }

        $total = $query->count();
        $completed = (clone $query)->where('status', 'completed')->count();
        $inTransit = (clone $query)->where('status', 'in_transit')->count();
        $cancelled = (clone $query)->where('status', 'cancelled')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'in_transit' => $inTransit,
            'cancelled' => $cancelled,
        ];
    }

    public function render()
    {
        return view('livewire.branches.transfer-index-component', [
            'transfers' => $this->transfers,
            'branches' => $this->branches,
            'stats' => $this->stats,
        ]);
    }
}
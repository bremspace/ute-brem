<?php

namespace App\Http\Livewire;

use App\Models\ServiceTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceTransactionIndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedStatus = '';
    public int $perPage = 15;

    protected $queryString = ['search', 'selectedStatus', 'perPage'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $tableReady = Schema::hasTable('service_transactions');

        $summary = [
            'total_count' => 0,
            'total_amount' => 0.0,
            'pending_count' => 0,
            'completed_count' => 0,
        ];

        if ($tableReady) {
            $summaryQuery = ServiceTransaction::query();
            $totals = $summaryQuery
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('COALESCE(SUM(grand_total), 0) as total_amount')
                ->selectRaw("SUM(CASE WHEN status = 'process' THEN 1 ELSE 0 END) as pending_count")
                ->selectRaw("SUM(CASE WHEN status IN ('done', 'taken') THEN 1 ELSE 0 END) as completed_count")
                ->first();

            if ($totals) {
                $summary = [
                    'total_count' => (int) $totals->total_count,
                    'total_amount' => (float) $totals->total_amount,
                    'pending_count' => (int) $totals->pending_count,
                    'completed_count' => (int) $totals->completed_count,
                ];
            }
        }

        $query = ServiceTransaction::query()
            ->with(['cashier:id,name', 'technician:id,name', 'customer'])
            ->latest('service_at');

        if (! empty($this->search)) {
            $query->where(function (Builder $q) {
                $q->where('service_code', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('device_brand', 'like', "%{$this->search}%")
                    ->orWhere('device_type', 'like', "%{$this->search}%");
            });
        }

        if (! empty($this->selectedStatus)) {
            $query->where('status', $this->selectedStatus);
        }

        $transactions = $query->paginate($this->perPage);

        return view('livewire.service-transactions.service-transaction-index-component', [
            'summary' => $summary,
            'transactions' => $transactions,
            'tableReady' => $tableReady,
        ]);
    }
}

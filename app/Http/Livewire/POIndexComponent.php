<?php

namespace App\Http\Livewire;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class POIndexComponent extends Component
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
        $summary = [
            'total_count' => 0,
            'total_value' => 0.0,
            'draft_count' => 0,
            'received_count' => 0,
        ];

        $totals = PurchaseOrder::query()
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('COALESCE(SUM(total_price), 0) as total_value')
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count")
            ->selectRaw("SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received_count")
            ->first();

        if ($totals) {
            $summary = [
                'total_count' => (int) $totals->total_count,
                'total_value' => (float) $totals->total_value,
                'draft_count' => (int) $totals->draft_count,
                'received_count' => (int) $totals->received_count,
            ];
        }

        $query = PurchaseOrder::query()
            ->with(['product:id,product_code,name,sale_unit', 'supplier:id,name', 'location:id,name'])
            ->latest('ordered_at');

        if (! empty($this->search)) {
            $query->where(function (Builder $q) {
                $q->where('po_number', 'like', "%{$this->search}%")
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$this->search}%"))
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$this->search}%"));
            });
        }

        if (! empty($this->selectedStatus)) {
            $query->where('status', $this->selectedStatus);
        }

        $purchaseOrders = $query->paginate($this->perPage);

        return view('livewire.purchase-orders.po-index-component', [
            'summary' => $summary,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }
}

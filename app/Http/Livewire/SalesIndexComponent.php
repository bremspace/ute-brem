<?php

namespace App\Http\Livewire;

use App\Models\Sale;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class SalesIndexComponent extends Component
{
    use WithPagination;

    public string $selectedDate = '';
    public string $saleChannel = 'toko';
    public string $search = '';
    public int $perPage = 15;

    public bool $showVoidModal = false;
    public ?int $saleIdToVoid = null;
    public string $voidReason = '';

    protected $queryString = ['selectedDate', 'saleChannel', 'search', 'perPage'];

    public function mount(string $saleChannel = 'toko'): void
    {
        $this->saleChannel = in_array($saleChannel, ['toko', 'cabang', 'partai'], true) ? $saleChannel : 'toko';
        $this->selectedDate = now()->toDateString();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedDate(): void
    {
        $this->resetPage();
    }

    public function setChannel(string $channel): void
    {
        $this->saleChannel = in_array($channel, ['toko', 'cabang', 'partai'], true) ? $channel : 'toko';
        $this->resetPage();
    }

    public function confirmVoid(int $saleId): void
    {
        $this->saleIdToVoid = $saleId;
        $this->voidReason = '';
        $this->showVoidModal = true;
    }

    public function executeVoid(): void
    {
        if (! $this->saleIdToVoid) {
            return;
        }

        $sale = Sale::with('items')->find($this->saleIdToVoid);
        if (! $sale || $sale->status !== 'paid') {
            session()->flash('error', 'Transaksi tidak dapat di-void.');
            $this->showVoidModal = false;
            return;
        }

        DB::transaction(function () use ($sale) {
            $oldStatus = $sale->status;
            $sale->update([
                'status' => 'void',
                'notes' => trim($sale->notes . ' [VOID: ' . ($this->voidReason ?: 'Dibatalkan kasir') . ']'),
            ]);

            UserLog::log(
                'VOID_TRANSACTION',
                "Void transaction: {$sale->sale_code}. Alasan: {$this->voidReason}",
                null,
                ['status' => $oldStatus],
                ['status' => 'void', 'reason' => $this->voidReason]
            );
        });

        session()->flash('success', "Transaksi {$sale->sale_code} berhasil di-void.");
        $this->showVoidModal = false;
        $this->saleIdToVoid = null;
        $this->voidReason = '';
    }

    public function render()
    {
        $date = Carbon::parse($this->selectedDate ?: now()->toDateString());

        $salesTableReady = Schema::hasTable('sales');

        $summary = [
            'transactions_count' => 0,
            'items_count' => 0,
            'grand_total' => 0.0,
            'discount_total' => 0.0,
        ];

        if ($salesTableReady) {
            $summaryQuery = Sale::query()
                ->whereDate('sale_at', $date->toDateString())
                ->where('status', 'paid');

            if ($this->saleChannel === 'toko') {
                $summaryQuery->where(function ($q) {
                    $q->where('sale_channel', 'toko')->orWhereNull('sale_channel');
                });
            } else {
                $summaryQuery->where('sale_channel', $this->saleChannel);
            }

            $totals = $summaryQuery
                ->selectRaw('COUNT(*) as transactions_count')
                ->selectRaw('COALESCE(SUM(items_count), 0) as items_count')
                ->selectRaw('COALESCE(SUM(grand_total), 0) as grand_total')
                ->selectRaw('COALESCE(SUM(discount_total), 0) as discount_total')
                ->first();

            if ($totals) {
                $summary = [
                    'transactions_count' => (int) $totals->transactions_count,
                    'items_count' => (int) $totals->items_count,
                    'grand_total' => (float) $totals->grand_total,
                    'discount_total' => (float) $totals->discount_total,
                ];
            }
        }

        $salesQuery = Sale::query()
            ->with(['customer:id,name,type', 'location:id,name', 'cashier:id,name', 'items'])
            ->whereDate('sale_at', $date->toDateString())
            ->latest('sale_at');

        if ($this->saleChannel === 'toko') {
            $salesQuery->where(function ($q) {
                $q->where('sale_channel', 'toko')->orWhereNull('sale_channel');
            });
        } else {
            $salesQuery->where('sale_channel', $this->saleChannel);
        }

        if (! empty($this->search)) {
            $salesQuery->where(function (Builder $q) {
                $q->where('sale_code', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
            });
        }

        $sales = $salesQuery->paginate($this->perPage);

        return view('livewire.sales.sales-index-component', [
            'summary' => $summary,
            'sales' => $sales,
            'selectedDate' => $date->toDateString(),
        ]);
    }
}

<?php

namespace App\Http\Livewire;

use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\ServiceTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsIndexComponent extends Component
{
    public function getTodaySalesProperty(): float
    {
        return (float) Sale::where('status', 'paid')
            ->whereDate('sale_at', now()->toDateString())
            ->sum('grand_total');
    }

    public function getTodayServicesProperty(): float
    {
        return (float) ServiceTransaction::where('status', '!=', 'cancelled')
            ->whereDate('service_at', now()->toDateString())
            ->sum('grand_total');
    }

    public function getLowStockCountProperty(): int
    {
        return ProductStock::where(function ($query) {
            $query->whereColumn('quantity', '<=', 'stock_min')
                ->orWhere('quantity', '<', 5);
        })->where('quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');
    }

    public function getTotalReceivablesProperty(): float
    {
        return (float) Sale::where('payment_method', 'tempo')
            ->where('credit_status', '!=', 'paid')
            ->sum(DB::raw('grand_total - paid_amount'));
    }

    public function render()
    {
        return view('livewire.reports.reports-index-component');
    }
}
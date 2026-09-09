<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $today = now()->toDateString();

        $salesReady = Schema::hasTable('sales');
        $saleItemsReady = Schema::hasTable('sale_items');
        $poReady = Schema::hasTable('purchase_orders');
        $cashReady = Schema::hasTable('cash_sessions');

        $todaySalesCount = 0;
        $todaySalesTotal = 0.0;
        $todayItemsCount = 0;
        $monthSalesTotal = 0.0;

        $chartLabels = [];
        $chartTotals = [];
        $chartCounts = [];

        $recentSales = collect();
        $recentPurchaseOrders = collect();
        $topProducts = collect();

        if ($salesReady) {
            $paidToday = Sale::query()
                ->where('status', 'paid')
                ->whereDate('sale_at', $today);

            $todaySalesCount = (int) $paidToday->count();
            $todaySalesTotal = (float) $paidToday->sum('grand_total');
            $todayItemsCount = (int) $paidToday->sum('items_count');

            $monthSalesTotal = (float) Sale::query()
                ->where('status', 'paid')
                ->whereBetween('sale_at', [now()->copy()->startOfMonth(), now()->copy()->endOfDay()])
                ->sum('grand_total');

            $from = now()->copy()->subDays(13)->startOfDay();
            $to = now()->copy()->endOfDay();

            $rows = Sale::query()
                ->where('status', 'paid')
                ->whereBetween('sale_at', [$from, $to])
                ->selectRaw('DATE(sale_at) as d, SUM(grand_total) as total, COUNT(*) as cnt')
                ->groupBy('d')
                ->orderBy('d')
                ->get()
                ->keyBy('d');

            $period = Carbon::parse($from)->daysUntil(Carbon::parse($to)->addDay());
            foreach ($period as $day) {
                $key = $day->toDateString();
                $chartLabels[] = $day->format('d/m');
                $chartTotals[] = (float) ($rows[$key]->total ?? 0);
                $chartCounts[] = (int) ($rows[$key]->cnt ?? 0);
            }

            $recentSales = Sale::query()
                ->with(['customer:id,name', 'location:id,name', 'cashier:id,name'])
                ->orderByDesc('sale_at')
                ->limit(8)
                ->get([
                    'id',
                    'sale_code',
                    'sale_at',
                    'customer_id',
                    'location_id',
                    'cashier_id',
                    'grand_total',
                    'status',
                ]);
        }

        if ($saleItemsReady && $salesReady) {
            $from = now()->copy()->subDays(6)->startOfDay();
            $to = now()->copy()->endOfDay();

            $topProducts = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.status', '=', 'paid')
                ->whereBetween('sales.sale_at', [$from, $to])
                ->selectRaw('sale_items.product_id, sale_items.product_name, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as total')
                ->groupBy('sale_items.product_id', 'sale_items.product_name')
                ->orderByDesc('qty')
                ->limit(6)
                ->get();
        }

        if ($poReady) {
            $recentPurchaseOrders = PurchaseOrder::query()
                ->with(['product:id,name,product_code', 'supplier:id,name', 'location:id,name'])
                ->orderByDesc('ordered_at')
                ->limit(6)
                ->get(['id', 'po_number', 'ordered_at', 'product_id', 'supplier_id', 'location_id', 'quantity']);
        }

        $lowStockProducts = Product::query()
            ->whereNotNull('stock_min')
            ->whereColumn('stock_global', '<=', 'stock_min')
            ->orderBy('stock_global')
            ->limit(10)
            ->get(['id', 'product_code', 'name', 'stock_global', 'stock_min', 'sale_unit']);

        $lowStockCount = Product::query()
            ->whereNotNull('stock_min')
            ->whereColumn('stock_global', '<=', 'stock_min')
            ->count();

        $cashSessions = collect();
        if ($cashReady) {
            $cashSessions = CashSession::query()
                ->with(['location:id,name'])
                ->whereDate('session_date', $today)
                ->where('cashier_id', auth()->id())
                ->orderByDesc('opened_at')
                ->limit(5)
                ->get(['id', 'session_date', 'location_id', 'opening_cash', 'status', 'opened_at']);
        }

        return view('home', [
            'tables' => [
                'sales' => $salesReady,
                'sale_items' => $saleItemsReady,
                'purchase_orders' => $poReady,
                'cash_sessions' => $cashReady,
            ],
            'today' => $today,
            'todaySalesCount' => $todaySalesCount,
            'todaySalesTotal' => $todaySalesTotal,
            'todayItemsCount' => $todayItemsCount,
            'monthSalesTotal' => $monthSalesTotal,
            'chartLabels' => $chartLabels,
            'chartTotals' => $chartTotals,
            'chartCounts' => $chartCounts,
            'recentSales' => $recentSales,
            'recentPurchaseOrders' => $recentPurchaseOrders,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'lowStockCount' => $lowStockCount,
            'cashSessions' => $cashSessions,
        ]);
    }
}

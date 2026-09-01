<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeCashAccount;
use App\Models\BackOfficeCashTransaction;
use App\Models\BackOfficeEmployeeAdvance;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\ServiceTransaction;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        // Simple aggregate dashboard counters for the cards
        $todayStr = now()->toDateString();

        $todaySales = Sale::where('status', 'paid')
            ->whereDate('sale_at', $todayStr)
            ->sum('grand_total');

        $todayServices = ServiceTransaction::where('status', '!=', 'cancelled')
            ->whereDate('service_at', $todayStr)
            ->sum('grand_total');

        $lowStockCount = ProductStock::where(function ($query) {
            $query->whereColumn('quantity', '<=', 'stock_min')
                ->orWhere('quantity', '<', 5);
        })->where('quantity', '>', 0)
            ->distinct('product_id')
            ->count('product_id');

        $totalReceivables = Sale::where('payment_method', 'tempo')
            ->where('credit_status', '!=', 'paid')
            ->sum(DB::raw('grand_total - paid_amount'));

        return view('reports.index', [
            'todaySales' => $todaySales,
            'todayServices' => $todayServices,
            'lowStockCount' => $lowStockCount,
            'totalReceivables' => $totalReceivables,
        ]);
    }

    public function sales(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
        $channel = $request->input('sale_channel');
        $paymentMethod = $request->input('payment_method');

        $query = Sale::with(['customer', 'location', 'cashier'])
            ->whereBetween('sale_at', [$startDate, $endDate])
            ->where('status', 'paid');

        if ($channel) {
            $query->where('sale_channel', $channel);
        }

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        // Summary Calculations
        $totalsQuery = clone $query;
        $totals = $totalsQuery->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('SUM(grand_total) as total_grand')
            ->selectRaw('SUM(subtotal) as total_sub')
            ->selectRaw('SUM(discount_total) as total_discount')
            ->selectRaw('SUM(paid_amount) as total_paid')
            ->first();

        $sales = $query->latest('sale_at')->paginate(25)->withQueryString();

        return view('reports.sales', [
            'sales' => $sales,
            'summary' => $totals,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'selectedChannel' => $channel,
            'selectedPaymentMethod' => $paymentMethod,
        ]);
    }

    public function purchases(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
        $supplierId = $request->input('supplier_id');

        $query = PurchaseOrder::with(['supplier', 'location', 'creator'])
            ->whereBetween('order_date', [$startDate, $endDate]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $totalsQuery = clone $query;
        $totals = $totalsQuery->selectRaw('COUNT(*) as total_pos')
            ->selectRaw('SUM(total_amount) as total_purchase_amount')
            ->first();

        $purchases = $query->latest('order_date')->paginate(25)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('reports.purchases', [
            'purchases' => $purchases,
            'summary' => $totals,
            'suppliers' => $suppliers,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'selectedSupplierId' => $supplierId,
        ]);
    }

    public function stocks(Request $request)
    {
        $locationId = $request->input('location_id');
        $search = $request->input('q');

        // Low stock warning list
        $lowStockQuery = ProductStock::with(['product', 'location'])
            ->where(function ($query) {
                $query->whereColumn('quantity', '<=', 'stock_min')
                    ->orWhere('quantity', '<', 5);
            });

        if ($locationId) {
            $lowStockQuery->where('location_id', $locationId);
        }

        $lowStocks = $lowStockQuery->get();

        // Stock search listing
        $stockQuery = ProductStock::with(['product', 'location', 'rack']);

        if ($locationId) {
            $stockQuery->where('location_id', $locationId);
        }

        if ($search) {
            $stockQuery->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        $stocks = $stockQuery->paginate(30)->withQueryString();

        // Recent stock movements
        $movements = StockMovement::with(['product', 'location', 'creator'])
            ->latest('movement_at')
            ->limit(15)
            ->get();

        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('reports.stocks', [
            'stocks' => $stocks,
            'lowStocks' => $lowStocks,
            'movements' => $movements,
            'locations' => $locations,
            'selectedLocationId' => $locationId,
        ]);
    }

    public function cash(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
        $accountId = $request->input('cash_account_id');

        $query = BackOfficeCashTransaction::with(['cashAccount', 'costCategory', 'customer'])
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($accountId) {
            $query->where('cash_account_id', $accountId);
        }

        $totalsQuery = clone $query;
        $inflow = (float) (clone $totalsQuery)->where('transaction_type', 'income')->sum('amount');
        $outflow = (float) (clone $totalsQuery)->where('transaction_type', 'expense')->sum('amount');

        $transactions = $query->latest('transaction_date')->paginate(25)->withQueryString();
        $accounts = BackOfficeCashAccount::where('is_active', true)->orderBy('name')->get();

        // Get Employee Advances (Kasbon)
        $advances = BackOfficeEmployeeAdvance::with(['employee', 'creator'])
            ->whereBetween('advance_date', [$startDate, $endDate])
            ->latest('advance_date')
            ->get();

        return view('reports.cash', [
            'transactions' => $transactions,
            'inflow' => $inflow,
            'outflow' => $outflow,
            'accounts' => $accounts,
            'advances' => $advances,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'selectedAccountId' => $accountId,
        ]);
    }

    public function receivables(Request $request)
    {
        $customerId = $request->input('customer_id');

        $query = Sale::with(['customer', 'location'])
            ->where('payment_method', 'tempo')
            ->where('status', 'paid');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        // Summary details
        $totalsQuery = clone $query;
        $totalCredit = $totalsQuery->sum('grand_total');
        $totalPaid = $totalsQuery->sum('paid_amount');
        $outstanding = $totalCredit - $totalPaid;

        $receivables = $query->latest('sale_at')->paginate(20)->withQueryString();
        
        // Fetch only customers with credit history
        $customerIdsWithTempo = Sale::where('payment_method', 'tempo')->distinct()->pluck('customer_id')->filter()->all();
        $customers = Customer::whereIn('id', $customerIdsWithTempo)->orderBy('name')->get();

        return view('reports.receivables', [
            'receivables' => $receivables,
            'totalCredit' => $totalCredit,
            'totalPaid' => $totalPaid,
            'outstanding' => $outstanding,
            'customers' => $customers,
            'selectedCustomerId' => $customerId,
        ]);
    }

    public function services(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();
        $technicianId = $request->input('technician_id');
        $status = $request->input('status');

        $query = ServiceTransaction::with(['technician', 'cashier'])
            ->whereBetween('service_at', [$startDate, $endDate]);

        if ($technicianId) {
            $query->where('technician_id', $technicianId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $totalsQuery = clone $query;
        $totalAmount = $totalsQuery->where('status', '!=', 'cancelled')->sum('grand_total');
        $totalCount = $totalsQuery->count();

        $services = $query->latest('service_at')->paginate(20)->withQueryString();
        
        // Technicians are Users
        $technicians = User::orderBy('name')->get();

        return view('reports.services', [
            'services' => $services,
            'totalAmount' => $totalAmount,
            'totalCount' => $totalCount,
            'technicians' => $technicians,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'selectedTechnicianId' => $technicianId,
            'selectedStatus' => $status,
        ]);
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : now()->endOfDay();

        // 1. Penjualan POS (Tunai, Transfer, QRIS, Tempo)
        $salesRevenue = (float) Sale::query()
            ->whereBetween('sale_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('subtotal');
        $salesHpp = (float) \App\Models\SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.sale_at', [$startDate, $endDate])
            ->where('sales.status', 'paid')
            ->sum(DB::raw('sale_items.quantity * sale_items.purchase_price'));

        $salesDiscount = (float) Sale::query()
            ->whereBetween('sale_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->sum('discount_total');

        // Net Sales = Sales Revenue - Sales Discount
        $netSales = max(0, $salesRevenue - $salesDiscount);

        // 2. Pendapatan Jasa Servis & Sparepart Servis
        $sparepartsRevenue = (float) \App\Models\ServiceTransactionItem::query()
            ->join('service_transactions', 'service_transactions.id', '=', 'service_transaction_items.service_transaction_id')
            ->whereBetween('service_transactions.service_at', [$startDate, $endDate])
            ->where('service_transactions.status', '!=', 'cancelled')
            ->where('service_transaction_items.item_type', 'product')
            ->sum('service_transaction_items.subtotal');

        $sparepartsHpp = (float) \App\Models\ServiceTransactionItem::query()
            ->join('service_transactions', 'service_transactions.id', '=', 'service_transaction_items.service_transaction_id')
            ->whereBetween('service_transactions.service_at', [$startDate, $endDate])
            ->where('service_transactions.status', '!=', 'cancelled')
            ->where('service_transaction_items.item_type', 'product')
            ->sum(DB::raw('service_transaction_items.quantity * service_transaction_items.purchase_price'));

        $laborRevenue = (float) \App\Models\ServiceTransactionItem::query()
            ->join('service_transactions', 'service_transactions.id', '=', 'service_transaction_items.service_transaction_id')
            ->whereBetween('service_transactions.service_at', [$startDate, $endDate])
            ->where('service_transactions.status', '!=', 'cancelled')
            ->where('service_transaction_items.item_type', 'service')
            ->sum('service_transaction_items.subtotal');

        // Total Revenue & Total HPP
        $totalRevenue = $netSales + $sparepartsRevenue + $laborRevenue;
        $totalHpp = $salesHpp + $sparepartsHpp;
        $grossProfit = $totalRevenue - $totalHpp;

        // 3. Pengeluaran Operasional (Operational Expenses)
        $expenses = (float) BackOfficeCashTransaction::query()
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('transaction_type', 'expense')
            ->where(function($q) {
                $q->whereNull('reference')
                  ->orWhere('reference', 'not like', 'PO-%');
            })
            ->where(function($q) {
                $q->whereNull('description')
                  ->orWhere('description', 'not like', 'Pembelian PO%');
            })
            ->sum('amount');

        $netProfit = $grossProfit - $expenses;

        return view('reports.profit-loss', [
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'salesRevenue' => $salesRevenue,
            'salesDiscount' => $salesDiscount,
            'netSales' => $netSales,
            'salesHpp' => $salesHpp,
            'sparepartsRevenue' => $sparepartsRevenue,
            'sparepartsHpp' => $sparepartsHpp,
            'laborRevenue' => $laborRevenue,
            'totalRevenue' => $totalRevenue,
            'totalHpp' => $totalHpp,
            'grossProfit' => $grossProfit,
            'expenses' => $expenses,
            'netProfit' => $netProfit,
        ]);
    }
}

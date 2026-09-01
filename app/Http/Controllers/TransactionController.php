<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerPointLedger;
use App\Models\CashSession;
use App\Models\Location;
use App\Models\PosSetting;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductUnit;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\TransactionPayment;
use App\Services\BackOfficeCashService;
use App\Services\StockLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public const SETTING_MEMBER_POINT_SPEND = 'member_point_spend_amount';
    public const SETTING_MEMBER_POINT_RESET_EVERY = 'member_point_reset_every';
    public const SETTING_MEMBER_POINT_RESET_UNIT = 'member_point_reset_unit'; // month|year
    public const SETTING_MEMBER_POINT_LAST_RESET_AT = 'member_point_last_reset_at';

    public function __construct(
        private readonly StockLedgerService $stockLedgerService,
        private readonly BackOfficeCashService $backOfficeCashService
    ) {
    }

    public function index(Request $request, string $saleChannel = 'toko')
    {
        $this->ensureMemberPointsReset();
        $saleChannel = $this->normalizeSaleChannel($saleChannel);

        $selectedDate = null;
        try {
            $selectedDate = $request->query('date')
                ? Carbon::parse($request->query('date'))->startOfDay()
                : now()->startOfDay();
        } catch (\Throwable $e) {
            $selectedDate = now()->startOfDay();
        }

        $salesTableReady = Schema::hasTable('sales');
        $recentSales = collect();
        $summary = [
            'date' => $selectedDate->toDateString(),
            'transactions_count' => 0,
            'items_count' => 0,
            'subtotal' => 0.0,
            'discount_total' => 0.0,
            'grand_total' => 0.0,
            'points_earned' => 0,
        ];

        if ($salesTableReady) {
            $totals = Sale::query()
                ->whereDate('sale_at', $selectedDate->toDateString())
                ->where('status', 'paid');
            $this->applySaleChannelFilter($totals, $saleChannel);
            $totals = $totals
                ->selectRaw('COUNT(*) AS transactions_count')
                ->selectRaw('COALESCE(SUM(items_count),0) AS items_count')
                ->selectRaw('COALESCE(SUM(subtotal),0) AS subtotal')
                ->selectRaw('COALESCE(SUM(discount_total),0) AS discount_total')
                ->selectRaw('COALESCE(SUM(grand_total),0) AS grand_total')
                ->selectRaw('COALESCE(SUM(points_earned),0) AS points_earned')
                ->first();

            if ($totals) {
                $summary = [
                    'date' => $selectedDate->toDateString(),
                    'transactions_count' => (int) ($totals->transactions_count ?? 0),
                    'items_count' => (int) ($totals->items_count ?? 0),
                    'subtotal' => (float) ($totals->subtotal ?? 0),
                    'discount_total' => (float) ($totals->discount_total ?? 0),
                    'grand_total' => (float) ($totals->grand_total ?? 0),
                    'points_earned' => (int) ($totals->points_earned ?? 0),
                ];
            }

            $recentSales = Sale::query()
                ->with(['customer:id,name,type', 'location:id,name', 'cashier:id,name', 'items:id,sale_id,product_name,product_code,quantity,unit_name,subtotal'])
                ->whereDate('sale_at', $selectedDate->toDateString());
            $this->applySaleChannelFilter($recentSales, $saleChannel);
            $recentSales = $recentSales
                ->latest('sale_at')
                ->limit(30)
                ->get();
        }

        return view('transactions.index', [
            'salesTableReady' => $salesTableReady,
            'settingsTableReady' => Schema::hasTable('pos_settings'),
            'memberPointSpendAmount' => $this->memberPointSpendAmount(),
            'memberPointResetEvery' => $this->memberPointResetEvery(),
            'memberPointResetUnit' => $this->memberPointResetUnit(),
            'selectedDate' => $selectedDate->toDateString(),
            'dailySummary' => $summary,
            'recentSales' => $recentSales,
            'saleChannel' => $saleChannel,
            'saleChannelLabel' => $this->saleChannelLabel($saleChannel),
        ]);
    }

    public function getData()
    {
        if (! Schema::hasTable('sales')) {
            return response()->json([
                'draw' => (int) request('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $filterDate = null;
        try {
            $filterDate = request('date') ? Carbon::parse((string) request('date'))->toDateString() : null;
        } catch (\Throwable $e) {
            $filterDate = null;
        }

        $saleChannel = $this->normalizeSaleChannel((string) (request('sale_channel') ?: request()->route('saleChannel') ?: 'toko'));

        $rows = Sale::query()
            ->with(['customer:id,name,type', 'location:id,name', 'cashier:id,name', 'items:id,sale_id,product_name,product_code,quantity,unit_name,subtotal'])
            ->select([
                'id',
                'sale_code',
                'sale_at',
                'customer_id',
                'location_id',
                'cashier_id',
                'items_count',
                'grand_total',
                'points_earned',
                'status',
                'created_at',
            ]);

        if ($filterDate) {
            $rows->whereDate('sale_at', $filterDate);
        }
        $this->applySaleChannelFilter($rows, $saleChannel);

        return datatables()->of($rows)
            ->addColumn('sale_code_link', fn (Sale $row) => '<a href="' . route('transactions.show', $row) . '" class="fw-semibold text-decoration-none text-primary">' . e($row->sale_code) . '</a>')
            ->addColumn('sale_at_label', fn (Sale $row) => optional($row->sale_at)->format('d M Y H:i') ?: '-')
            ->addColumn('customer_label', fn (Sale $row) => $row->customer?->name ?: '-')
            ->addColumn('location_label', fn (Sale $row) => $row->location?->name ?: '-')
            ->addColumn('cashier_label', fn (Sale $row) => $row->cashier?->name ?: '-')
            ->addColumn('grand_total_label', fn (Sale $row) => 'Rp ' . number_format((float) $row->grand_total, 0, ',', '.'))
            ->addColumn('items_summary', fn (Sale $row) => $this->formatItemsSummary($row))
            ->addColumn('status_badge', function (Sale $row) {
                $label = $row->status === 'paid' ? 'Paid' : 'Void';
                $color = $row->status === 'paid' ? 'success' : 'secondary';

                return '<span class="badge bg-label-' . $color . '">' . $label . '</span>';
            })
            ->addColumn('action', fn (Sale $row) => '<a href="' . route('transactions.show', $row) . '" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>View</a>')
            ->rawColumns(['sale_code_link', 'items_summary', 'status_badge', 'action'])
            ->make(true);
    }

    public function create(Request $request, string $saleChannel = 'toko')
    {
        $this->ensureMemberPointsReset();
        $saleChannel = $this->normalizeSaleChannel($saleChannel);
        
        $user = auth()->user();
        $query = Location::where('is_active', true);
        if ($user && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        $locations = $query->orderBy('name')->get();

        return view('transactions.create', [
            'salesTableReady' => Schema::hasTable('sales'),
            'customersTableReady' => Schema::hasTable('customers'),
            'settingsTableReady' => Schema::hasTable('pos_settings'),
            'locations' => $locations,
            'defaultLocationId' => $locations->first()?->id,
            'memberPointSpendAmount' => $this->memberPointSpendAmount(),
            'draftSaleCode' => $this->generateSaleCode(),
            'saleChannel' => $saleChannel,
            'saleChannelLabel' => $this->saleChannelLabel($saleChannel),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureMemberPointsReset();

        if (! Schema::hasTable('sales')) {
            return redirect()->route('transactions.index')
                ->with('error', 'Fitur transaksi belum siap. Silakan jalankan migration terlebih dahulu.');
        }

        $validated = $request->validate([
            'sale_code' => ['required', 'string', 'max:80', Rule::unique('sales', 'sale_code')],
            'sale_channel' => ['nullable', Rule::in(['toko', 'cabang', 'partai'])],
            'sale_at' => 'required|date',
            'location_id' => 'required|exists:locations,id',
            'customer_id' => Schema::hasTable('customers') ? 'nullable|exists:customers,id' : 'nullable',
            'notes' => 'nullable|string|max:2000',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris', 'tempo'])],
            'payment_reference' => 'nullable|string|max:120',
            'credit_term_days' => 'nullable|integer|min:1|max:3650',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.barcode' => 'nullable|string|max:120',
            'items.*.unit_level' => 'nullable|integer|min:1|max:10',
            'items.*.unit_name' => 'nullable|string|max:50',
            'items.*.conversion_qty' => 'nullable|numeric|min:0.01',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_value' => 'nullable|numeric|min:0',
            'items.*.serial_numbers' => 'nullable|array',
            'items.*.serial_numbers.*' => 'nullable|string|max:120',
        ]);
        $saleChannel = $this->normalizeSaleChannel($validated['sale_channel'] ?? 'toko');

        $user = auth()->user();
        $query = Location::where('is_active', true);
        if ($user && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        $location = $query->findOrFail($validated['location_id']);
        $customer = null;
        if (! empty($validated['customer_id']) && Schema::hasTable('customers')) {
            $customer = Customer::find($validated['customer_id']);
        }

        $cashSession = null;
        if (Schema::hasTable('cash_sessions')) {
            $cashSession = CashSession::query()
                ->where('session_date', now()->toDateString())
                ->where('location_id', $location->id)
                ->where('cashier_id', auth()->id())
                ->where('status', 'open')
                ->first();
            if (! $cashSession) {
                return back()->withInput()->with('error', 'Masukkan kas awal dulu sebelum transaksi.');
            }
        }

        $itemsInput = $validated['items'];
        $productIds = collect($itemsInput)->pluck('product_id')->unique()->values()->all();
        $products = Product::query()
            ->with(['units', 'barcodes'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $itemsCount = 0;
        $subtotal = 0.0;
        $discountTotal = 0.0;
        $saleItemsPayload = [];

        foreach ($itemsInput as $row) {
            $product = $products->get($row['product_id']);
            if (! $product) {
                continue;
            }

            $unitLevel = (int) ($row['unit_level'] ?? 1);
            $unitRow = $product->units?->firstWhere('level', $unitLevel);
            $resolvedUnitName = $unitRow?->unit_name ?: ($row['unit_name'] ?? ($product->sale_unit ?: null));
            $resolvedConversionQty = (float) ($unitRow?->conversion_qty ?: ($row['conversion_qty'] ?? 1.0));
            $resolvedUnitPrice = null;

            $customerGroupId = $customer?->customer_group_id ?? null;
            if ($unitRow) {
                $resolvedUnitPrice = $this->resolveProductUnitPrice($product, $unitRow, $saleChannel, $customerGroupId);
            } else {
                $resolvedUnitPrice = $this->resolveProductUnitPrice($product, null, $saleChannel, $customerGroupId);
            }

            $quantity = (float) $row['quantity'];
            $conversionQty = $resolvedConversionQty > 0 ? $resolvedConversionQty : 1.0;
            $baseQuantity = $quantity * $conversionQty;
            $unitPrice = $product->is_open_price ? (float) $row['unit_price'] : $resolvedUnitPrice;
            $discountValue = (float) ($row['discount_value'] ?? 0);
            $lineSubtotal = max(0, ($quantity * $unitPrice) - $discountValue);

            $itemsCount += 1;
            $subtotal += ($quantity * $unitPrice);
            $discountTotal += $discountValue;

            $saleItemsPayload[] = [
                'product_id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->name,
                'barcode' => $row['barcode'] ?? $product->primaryBarcode(),
                'unit_level' => $unitLevel,
                'unit_name' => $resolvedUnitName,
                'conversion_qty' => $conversionQty,
                'quantity' => $quantity,
                'base_quantity' => $baseQuantity,
                'unit_price' => $unitPrice,
                'purchase_price' => (float) ($product->purchase_price ?: 0) * $conversionQty,
                'discount_value' => $discountValue,
                'subtotal' => $lineSubtotal,
                'serial_numbers' => $row['serial_numbers'] ?? null,
            ];
        }

        if (count($saleItemsPayload) === 0) {
            return back()->withInput()->with('error', 'Item transaksi tidak valid.');
        }

        $grandTotal = max(0, $subtotal - $discountTotal);
        $paymentMethod = $validated['payment_method'];
        $paidAmount = (float) $validated['paid_amount'];
        $isTempo = $paymentMethod === 'tempo';

        if ($isTempo && ! $customer) {
            return back()->withInput()->with('error', 'Pembayaran tempo hanya bisa untuk customer terdaftar.');
        }

        if ($isTempo && empty($validated['credit_term_days'])) {
            return back()->withInput()->with('error', 'Lama tempo wajib diisi.');
        }

        if (! $isTempo && $paidAmount < $grandTotal) {
            return back()->withInput()->with('error', 'Jumlah bayar kurang dari total belanja.');
        }

        if ($isTempo && $paidAmount > $grandTotal) {
            return back()->withInput()->with('error', 'Bayar awal tempo tidak boleh lebih besar dari grand total.');
        }

        $changeAmount = $isTempo ? 0 : ($paidAmount - $grandTotal);
        $creditTermDays = $isTempo ? (int) $validated['credit_term_days'] : null;
        $creditDueAt = $isTempo ? Carbon::parse($validated['sale_at'])->addDays($creditTermDays)->toDateString() : null;
        $creditStatus = ! $isTempo
            ? 'paid'
            : ($paidAmount >= $grandTotal ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'));

        $saleCode = $validated['sale_code'];

        try {
            $sale = DB::transaction(function () use ($validated, $saleChannel, $location, $customer, $cashSession, $saleCode, $itemsCount, $subtotal, $discountTotal, $grandTotal, $paidAmount, $changeAmount, $creditTermDays, $creditDueAt, $creditStatus, $saleItemsPayload) {
                $sale = Sale::create([
                    'sale_code' => $saleCode,
                    'sale_channel' => $saleChannel,
                    'sale_at' => $validated['sale_at'],
                    'location_id' => $location->id,
                    'customer_id' => $customer?->id,
                    'cashier_id' => auth()->id(),
                    'cash_session_id' => $cashSession?->id,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paidAmount,
                    'change_amount' => $changeAmount,
                    'payment_method' => $validated['payment_method'],
                    'payment_reference' => $validated['payment_reference'] ?? null,
                    'credit_term_days' => $creditTermDays,
                    'credit_due_at' => $creditDueAt,
                    'credit_status' => $creditStatus,
                    'items_count' => $itemsCount,
                    'points_earned' => 0,
                    'status' => 'paid',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($saleItemsPayload as $payload) {
                    SaleItem::create(array_merge($payload, ['sale_id' => $sale->id]));
                }

                $sale->load(['items.product', 'customer']);

                // Deduct stock per line in base unit.
                foreach ($sale->items as $item) {
                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $location,
                        StockLedgerService::TYPE_OUT,
                        (float) $item->base_quantity,
                        'Penjualan ' . $sale->sale_code,
                        $sale->sale_at,
                        'sale',
                        $sale->sale_code
                    );
                }

                // Member points: based on grand total.
                if ($sale->customer && method_exists($sale->customer, 'isMember') && $sale->customer->isMember()) {
                    $points = $this->calculateMemberPoints($grandTotal);
                    if ($points > 0 && Schema::hasColumn('customers', 'points_balance')) {
                        $balanceAfter = (int) ($sale->customer->points_balance ?? 0) + $points;
                        $sale->customer->update(['points_balance' => $balanceAfter]);

                        if (Schema::hasTable('customer_point_ledgers')) {
                            CustomerPointLedger::create([
                                'customer_id' => $sale->customer->id,
                                'sale_id' => $sale->id,
                                'points' => $points,
                                'balance_after' => $balanceAfter,
                                'source' => 'sale',
                                'reference_code' => $sale->sale_code,
                                'notes' => 'Poin dari transaksi penjualan.',
                                'created_by' => auth()->id(),
                            ]);
                        }

                        $sale->update(['points_earned' => $points]);
                    }
                }

                // Record cash inflow to backoffice cash accounts
                $cashAmount = ($sale->payment_method === 'tempo') ? (float) $sale->paid_amount : (float) $sale->grand_total;
                if ($cashAmount > 0) {
                    $this->backOfficeCashService->recordPOSInflow(
                        $cashAmount,
                        $sale->payment_method,
                        $sale->sale_code,
                        $sale->customer,
                        'Pendapatan POS ' . $sale->sale_code,
                        Carbon::parse($sale->sale_at)->toDateString()
                    );
                }

                return $sale->fresh(['items', 'customer', 'location', 'cashier']);
            });
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        $redirectUrl = route('transactions.create.channel', $saleChannel);

        return redirect()
            ->to($redirectUrl)
            ->with('success', 'Transaksi berhasil disimpan.')
            ->with('last_sale_id', $sale->id)
            ->with('last_sale_code', $sale->sale_code)
            ->with('last_receipt_url', route('transactions.receipt', [$sale, 'print' => 1]));
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'customer', 'location', 'cashier', 'payments.receiver']);

        return view('transactions.show', [
            'sale' => $sale,
            'memberPointSpendAmount' => $this->memberPointSpendAmount(),
        ]);
    }

    public function storePayment(Request $request, Sale $sale)
    {
        if ($sale->status === 'void') {
            return back()->with('error', 'Transaksi void tidak bisa menerima pelunasan.');
        }

        if (! $sale->customer_id) {
            return back()->with('error', 'Pelunasan tempo hanya bisa untuk transaksi dengan customer terdaftar.');
        }

        $validated = $request->validate([
            'payment_at' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris'])],
            'reference' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $sale->load('payments');
        $outstanding = max(0, (float) $sale->grand_total - (float) $sale->paid_amount);
        $amount = (float) $validated['amount'];

        if ($outstanding <= 0) {
            return back()->with('error', 'Transaksi ini sudah lunas.');
        }

        if ($amount > $outstanding) {
            return back()->withInput()->with('error', 'Nominal pelunasan lebih besar dari sisa tempo.');
        }

        DB::transaction(function () use ($sale, $validated, $amount) {
            TransactionPayment::create([
                'sale_id' => $sale->id,
                'service_transaction_id' => null,
                'customer_id' => $sale->customer_id,
                'payment_at' => $validated['payment_at'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            $paid = (float) $sale->paid_amount + $amount;
            $sale->update([
                'paid_amount' => $paid,
                'change_amount' => 0,
                'credit_status' => $paid >= (float) $sale->grand_total ? 'paid' : 'partial',
            ]);

            $sale->loadMissing('customer');
            $this->backOfficeCashService->recordPaymentInflow(
                $amount,
                $validated['payment_method'],
                $sale->sale_code,
                $sale->customer,
                'Pelunasan tempo POS ' . $sale->sale_code,
                Carbon::parse($validated['payment_at'])->toDateString()
            );
        });

        return back()->with('success', 'Pelunasan tempo berhasil dicatat.');
    }

    public function updateSerialNumbers(Request $request, Sale $sale)
    {
        if ($sale->status === 'void') {
            return back()->with('error', 'Serial number tidak bisa diubah untuk transaksi void.');
        }

        $validated = $request->validate([
            'serial_numbers' => 'nullable|array',
            'serial_numbers.*' => 'nullable|string|max:5000',
        ]);

        $sale->load('items');
        $serialRows = $validated['serial_numbers'] ?? [];

        DB::transaction(function () use ($sale, $serialRows) {
            foreach ($sale->items as $item) {
                if (! array_key_exists($item->id, $serialRows)) {
                    continue;
                }

                $serials = collect(preg_split('/\r\n|\r|\n/', (string) $serialRows[$item->id]))
                    ->map(fn ($serial) => trim((string) $serial))
                    ->filter()
                    ->values()
                    ->all();

                $item->update([
                    'serial_numbers' => $serials ?: null,
                ]);
            }
        });

        return back()->with('success', 'Serial number berhasil disimpan.');
    }

    public function void(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'void_reason' => 'nullable|string|max:500',
        ]);

        if ($sale->status === 'void') {
            return back()->with('error', 'Transaksi sudah void.');
        }

        $sale->load(['items.product', 'customer', 'location']);

        if (! $sale->location) {
            return back()->with('error', 'Lokasi transaksi tidak ditemukan, stok tidak bisa dikembalikan.');
        }

        try {
            DB::transaction(function () use ($sale, $validated) {
                foreach ($sale->items as $item) {
                    if (! $item->product) {
                        continue;
                    }

                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $sale->location,
                        StockLedgerService::TYPE_IN,
                        (float) $item->base_quantity,
                        'Void transaksi ' . $sale->sale_code,
                        now(),
                        'sale_void',
                        $sale->sale_code
                    );
                }

                if ($sale->customer && (int) $sale->points_earned > 0 && Schema::hasColumn('customers', 'points_balance')) {
                    $currentBalance = (int) ($sale->customer->points_balance ?? 0);
                    $pointsToReverse = min($currentBalance, (int) $sale->points_earned);
                    $balanceAfter = $currentBalance - $pointsToReverse;
                    $sale->customer->update(['points_balance' => $balanceAfter]);

                    if ($pointsToReverse > 0 && Schema::hasTable('customer_point_ledgers')) {
                        CustomerPointLedger::create([
                            'customer_id' => $sale->customer->id,
                            'sale_id' => $sale->id,
                            'points' => -$pointsToReverse,
                            'balance_after' => $balanceAfter,
                            'source' => 'void',
                            'reference_code' => $sale->sale_code,
                            'notes' => 'Pembatalan poin dari transaksi void.',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }

                $reason = trim((string) ($validated['void_reason'] ?? ''));
                $notes = trim((string) ($sale->notes ?? ''));
                $voidNote = 'VOID ' . now()->format('d/m/Y H:i') . ($reason !== '' ? ': ' . $reason : '');

                $sale->update([
                    'status' => 'void',
                    'points_earned' => 0,
                    'notes' => trim($notes . "\n" . $voidNote),
                ]);

                // Reverse cash inflow to backoffice cash accounts
                $cashAmount = ($sale->payment_method === 'tempo') ? (float) $sale->paid_amount : (float) $sale->grand_total;
                if ($cashAmount > 0) {
                    $this->backOfficeCashService->reversePOSInflow(
                        $cashAmount,
                        $sale->payment_method,
                        $sale->sale_code,
                        $sale->customer,
                        'Void transaksi POS ' . $sale->sale_code
                    );
                }

                // Reverse any subsequent installment payments
                if ($sale->payment_method === 'tempo') {
                    $sale->loadMissing('payments');
                    foreach ($sale->payments as $payment) {
                        $this->backOfficeCashService->reversePOSInflow(
                            (float) $payment->amount,
                            $payment->payment_method,
                            $sale->sale_code,
                            $sale->customer,
                            'Void pelunasan tempo POS ' . $sale->sale_code
                        );
                    }
                }
            });
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('transactions.show', $sale)->with('success', 'Transaksi berhasil di-void dan stok sudah dikembalikan.');
    }

    public function receipt(Request $request, Sale $sale)
    {
        $sale->load(['items.product', 'customer', 'location', 'cashier']);

        $embed = $request->boolean('embed');

        $paperWidth = max(58, min(80, PosSetting::getInt(\App\Http\Controllers\PrinterSettingController::KEY_PRINTER_PAPER_WIDTH, 80)));
        $copies = max(1, PosSetting::getInt(\App\Http\Controllers\PrinterSettingController::KEY_PRINTER_COPIES, 1));
        $headerText = PosSetting::getString(\App\Http\Controllers\PrinterSettingController::KEY_PRINTER_HEADER_TEXT, '');
        $footerText = PosSetting::getString(\App\Http\Controllers\PrinterSettingController::KEY_PRINTER_FOOTER_TEXT, 'Terima kasih.');
        $showStoreName = (bool) PosSetting::getInt(\App\Http\Controllers\PrinterSettingController::KEY_PRINTER_SHOW_STORE_NAME, 1);
        $showDatetime = (bool) PosSetting::getInt(\App\Http\Controllers\PrinterSettingController::KEY_PRINTER_SHOW_DATETIME, 1);
        $autoPrint = $request->boolean('print')
            || (bool) PosSetting::getInt(\App\Http\Controllers\PrinterSettingController::KEY_PRINTER_AUTO_PRINT, 0);
        $company = \App\Http\Controllers\PrinterSettingController::companySettings();

        return view('transactions.receipt', [
            'sale' => $sale,
            'embed' => $embed,
            'company' => $company,
            'printer' => [
                'paper_width_mm' => in_array($paperWidth, [58, 80], true) ? $paperWidth : 80,
                'copies' => $copies,
                'header_text' => $headerText,
                'footer_text' => $footerText,
                'show_store_name' => $showStoreName,
                'show_datetime' => $showDatetime,
                'auto_print' => $autoPrint,
            ],
        ]);
    }

    public function updateMemberPointSetting(Request $request)
    {
        if (! Schema::hasTable('pos_settings')) {
            return response()->json([
                'message' => 'Pengaturan belum tersedia. Jalankan migration terlebih dahulu.',
            ], 409);
        }

        $validated = $request->validate([
            'member_point_spend_amount' => 'required|integer|min:1',
            'member_point_reset_every' => 'nullable|integer|min:0|max:120',
            'member_point_reset_unit' => ['nullable', Rule::in(['month', 'year'])],
        ]);

        PosSetting::setString(self::SETTING_MEMBER_POINT_SPEND, (string) $validated['member_point_spend_amount']);
        PosSetting::setString(self::SETTING_MEMBER_POINT_RESET_EVERY, (string) ($validated['member_point_reset_every'] ?? 0));
        PosSetting::setString(self::SETTING_MEMBER_POINT_RESET_UNIT, (string) ($validated['member_point_reset_unit'] ?? 'month'));

        if (! PosSetting::getString(self::SETTING_MEMBER_POINT_LAST_RESET_AT, null)) {
            PosSetting::setString(self::SETTING_MEMBER_POINT_LAST_RESET_AT, now()->toDateTimeString());
        }

        return response()->json([
            'message' => 'Setting poin member berhasil disimpan.',
            'member_point_spend_amount' => (int) $validated['member_point_spend_amount'],
            'member_point_reset_every' => (int) ($validated['member_point_reset_every'] ?? 0),
            'member_point_reset_unit' => (string) ($validated['member_point_reset_unit'] ?? 'month'),
        ]);
    }

    public function cashSessionStatus(Request $request)
    {
        if (! Schema::hasTable('cash_sessions')) {
            return response()->json(['enabled' => false]);
        }

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'session_date' => 'nullable|date',
        ]);

        $date = $validated['session_date'] ?? now()->toDateString();

        $session = CashSession::query()
            ->where('session_date', $date)
            ->where('location_id', $validated['location_id'])
            ->where('cashier_id', auth()->id())
            ->where('status', 'open')
            ->first();

        return response()->json([
            'enabled' => true,
            'has_session' => (bool) $session,
            'opening_cash' => $session ? (float) $session->opening_cash : null,
        ]);
    }

    public function openCashSession(Request $request)
    {
        if (! Schema::hasTable('cash_sessions')) {
            return response()->json([
                'message' => 'Tabel cash_sessions belum tersedia. Jalankan migration terlebih dahulu.',
            ], 409);
        }

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'session_date' => 'required|date',
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $session = CashSession::query()->updateOrCreate(
            [
                'session_date' => $validated['session_date'],
                'location_id' => $validated['location_id'],
                'cashier_id' => auth()->id(),
            ],
            [
                'opening_cash' => (float) $validated['opening_cash'],
                'opened_at' => now(),
                'status' => 'open',
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Kas awal berhasil disimpan.',
            'opening_cash' => (float) $session->opening_cash,
        ]);
    }

    public function cashSessionSummary(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        $session = CashSession::query()
            ->where('session_date', now()->toDateString())
            ->where('location_id', $validated['location_id'])
            ->where('cashier_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return response()->json(['has_session' => false], 404);
        }

        $cashSales = (float) Sale::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'cash')
            ->where('status', 'paid')
            ->sum('grand_total');

        $tempoCashSales = (float) Sale::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'tempo')
            ->where('status', 'paid')
            ->sum('paid_amount');

        $transferSales = (float) Sale::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'transfer')
            ->where('status', 'paid')
            ->sum('grand_total');

        $qrisSales = (float) Sale::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'qris')
            ->where('status', 'paid')
            ->sum('grand_total');

        $cashServices = (float) ServiceTransaction::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'cash')
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        $tempoCashServices = (float) ServiceTransaction::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'tempo')
            ->where('status', '!=', 'cancelled')
            ->sum('paid_amount');

        $transferServices = (float) ServiceTransaction::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'transfer')
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        $qrisServices = (float) ServiceTransaction::query()
            ->where('cash_session_id', $session->id)
            ->where('payment_method', 'qris')
            ->where('status', '!=', 'cancelled')
            ->sum('grand_total');

        $creditPayments = \App\Models\TransactionPayment::query()
            ->where('received_by', auth()->id())
            ->where('payment_at', '>=', $session->opened_at)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        $creditCash = (float) ($creditPayments['cash'] ?? 0);
        $creditTransfer = (float) ($creditPayments['transfer'] ?? 0);
        $creditQris = (float) ($creditPayments['qris'] ?? 0);

        $totalCash = $cashSales + $tempoCashSales + $cashServices + $tempoCashServices + $creditCash;
        $totalTransfer = $transferSales + $transferServices + $creditTransfer;
        $totalQris = $qrisSales + $qrisServices + $creditQris;

        $openingCash = (float) $session->opening_cash;
        $expectedClosing = $openingCash + $totalCash;

        return response()->json([
            'has_session' => true,
            'opened_at' => $session->opened_at,
            'opening_cash' => $openingCash,
            'sales' => [
                'cash' => $cashSales,
                'tempo_cash' => $tempoCashSales,
                'transfer' => $transferSales,
                'qris' => $qrisSales,
            ],
            'services' => [
                'cash' => $cashServices,
                'tempo_cash' => $tempoCashServices,
                'transfer' => $transferServices,
                'qris' => $qrisServices,
            ],
            'credit_payments' => [
                'cash' => $creditCash,
                'transfer' => $creditTransfer,
                'qris' => $creditQris,
            ],
            'aggregates' => [
                'total_cash' => $totalCash,
                'total_transfer' => $totalTransfer,
                'total_qris' => $totalQris,
            ],
            'expected_closing_cash' => $expectedClosing,
        ]);
    }

    public function closeCashSession(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $session = CashSession::query()
            ->where('session_date', now()->toDateString())
            ->where('location_id', $validated['location_id'])
            ->where('cashier_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (! $session) {
            return response()->json([
                'message' => 'Tidak ditemukan sesi kas aktif untuk hari ini.',
            ], 404);
        }

        $closingCash = (float) $validated['closing_cash'];
        $openingCash = (float) $session->opening_cash;

        DB::transaction(function () use ($session, $closingCash, $openingCash, $validated) {
            $session->update([
                'closing_cash' => $closingCash,
                'closed_at' => now(),
                'status' => 'closed',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Setor otomatis ke kas utama jika ada beda akun (KASIR -> UTAMA)
            $sourceAccount = BackOfficeCashAccount::where('is_active', true)
                ->where(fn ($q) => $q->where('code', 'like', '%KASIR%')->orWhere('code', 'like', '%TOKO%'))
                ->first();

            $targetAccount = BackOfficeCashAccount::where('is_active', true)
                ->where(fn ($q) => $q->where('code', 'like', '%UTAMA%')->orWhere('code', 'like', '%MAIN%'))
                ->first();

            if ($sourceAccount && $targetAccount && $sourceAccount->id !== $targetAccount->id) {
                $depositAmount = $closingCash - $openingCash;
                if ($depositAmount > 0) {
                    $source = BackOfficeCashAccount::lockForUpdate()->find($sourceAccount->id);
                    $target = BackOfficeCashAccount::lockForUpdate()->find($targetAccount->id);

                    if ($source->current_balance >= $depositAmount) {
                        $source->decrement('current_balance', $depositAmount);
                        $target->increment('current_balance', $depositAmount);

                        BackOfficeCashTransaction::create([
                            'transaction_code' => 'CSH-SET-' . now()->format('ymdHis') . '-' . random_int(100, 999),
                            'transaction_date' => now()->toDateString(),
                            'transaction_type' => 'mutation',
                            'cash_account_id' => $source->id,
                            'target_cash_account_id' => $target->id,
                            'amount' => $depositAmount,
                            'description' => 'Setoran Kasir harian (' . auth()->user()->name . ')',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Kasir berhasil ditutup.',
            'closing_cash' => $closingCash,
        ]);
    }

    public function lookupProduct(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|max:120',
            'location_id' => 'nullable|integer',
            'sale_channel' => ['nullable', Rule::in(['toko', 'cabang', 'partai'])],
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        $q = trim($validated['q']);
        $locationId = $validated['location_id'] ?? null;
        $saleChannel = $this->normalizeSaleChannel($validated['sale_channel'] ?? 'toko');
        $customerId = $validated['customer_id'] ?? null;

        $customerGroupId = null;
        if ($customerId) {
            $customerGroupId = Customer::where('id', $customerId)->value('customer_group_id');
        }

        $product = null;
        $unitLevel = 1;
        $unitName = null;
        $conversionQty = 1.0;
        $barcode = $q;
        $unitPrice = null;

        $barcodeRow = ProductBarcode::query()->where('barcode', $q)->first();
        if ($barcodeRow) {
            $product = Product::query()->with(['stocks', 'units'])->find($barcodeRow->product_id);
            $unitLevel = (int) ($barcodeRow->unit_level ?: 1);

            $unitRow = $product?->units?->firstWhere('level', $unitLevel);
            if ($unitRow) {
                $unitName = $unitRow->unit_name;
                $conversionQty = (float) ($unitRow->conversion_qty ?: 1);
                $unitPrice = $this->resolveProductUnitPrice($product, $unitRow, $saleChannel, $customerGroupId);
            }
        }

        if (! $product) {
            $unitRow = ProductUnit::query()->where('barcode', $q)->first();
            if ($unitRow) {
                $product = Product::query()->with(['stocks', 'units'])->find($unitRow->product_id);
                $unitLevel = (int) $unitRow->level;
                $unitName = $unitRow->unit_name;
                $conversionQty = (float) ($unitRow->conversion_qty ?: 1);
                $unitPrice = $this->resolveProductUnitPrice($product, $unitRow, $saleChannel, $customerGroupId);
            }
        }

        if (! $product) {
            $product = Product::query()
                ->with(['stocks', 'units'])
                ->where('barcode', $q)
                ->orWhere('product_code', $q)
                ->orWhere('sku', $q)
                ->first();
        }

        if (! $product) {
            return response()->json(['found' => false], 404);
        }

        if ($unitPrice === null) {
            $unitPrice = $this->resolveProductUnitPrice($product, null, $saleChannel, $customerGroupId);
        }

        if ($unitName === null) {
            $unitName = $product->sale_unit ?: null;
        }

        $stockAtLocation = null;
        if ($locationId) {
            $stock = $product->stocks->firstWhere('location_id', (int) $locationId);
            $stockAtLocation = $stock ? (float) $stock->quantity : 0.0;
        }

        return response()->json([
            'found' => true,
            'product' => [
                'id' => $product->id,
                'product_code' => $product->product_code,
                'name' => $product->name,
                'has_serial_number' => (bool) $product->has_serial_number,
                'is_open_price' => (bool) $product->is_open_price,
                'barcode' => $barcodeRow?->barcode ?: ($product->primaryBarcode() ?: $q),
                'unit_level' => $unitLevel,
                'unit_name' => $unitName,
                'conversion_qty' => $conversionQty,
                'unit_price' => $unitPrice,
                'stock_at_location' => $stockAtLocation,
            ],
        ]);
    }

    public function searchProducts(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:120',
            'location_id' => 'nullable|integer|exists:locations,id',
            'limit' => 'nullable|integer|min:1|max:50',
            'sale_channel' => ['nullable', Rule::in(['toko', 'cabang', 'partai'])],
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        $locationId = $validated['location_id'] ?? null;
        $limit = (int) ($validated['limit'] ?? 25);
        $saleChannel = $this->normalizeSaleChannel($validated['sale_channel'] ?? 'toko');
        $customerId = $validated['customer_id'] ?? null;

        $customerGroupId = null;
        if ($customerId) {
            $customerGroupId = Customer::where('id', $customerId)->value('customer_group_id');
        }

        $rows = Product::query()
            ->with(['images:id,product_id,image_path,sort_order', 'units:id,product_id,level,unit_name,price_toko,price_partai,price_cabang'])
            ->where('products.is_active', true)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('products.name', 'like', '%' . $q . '%')
                        ->orWhere('products.product_code', 'like', '%' . $q . '%')
                        ->orWhere('products.barcode', 'like', '%' . $q . '%')
                        ->orWhere('products.sku', 'like', '%' . $q . '%');
                });
            })
            ->leftJoin('product_suppliers as ps', function ($join) {
                $join->on('ps.product_id', '=', 'products.id')->where('ps.is_primary', '=', 1);
            })
            ->leftJoin('suppliers as s', 's.id', '=', 'ps.supplier_id')
            ->when($locationId, function ($query) use ($locationId) {
                $query->leftJoin('product_stocks as stk', function ($join) use ($locationId) {
                    $join->on('stk.product_id', '=', 'products.id')->where('stk.location_id', '=', $locationId);
                });
            })
            ->select([
                'products.id',
                'products.product_code',
                'products.name',
                'products.selling_price',
                'products.sale_unit',
                'products.has_serial_number',
                'products.is_open_price',
                'products.legacy_image_path',
                DB::raw('COALESCE(s.name, \'-\') as supplier_name'),
                DB::raw($locationId ? 'COALESCE(stk.quantity, 0) as stock_at_location' : 'NULL as stock_at_location'),
            ])
            ->orderBy('products.name')
            ->limit($limit)
            ->get();

        return response()->json([
            'results' => $rows->map(function ($row) use ($saleChannel, $customerGroupId) {
                return [
                    'id' => (int) $row->id,
                    'product_code' => $row->product_code,
                    'name' => $row->name,
                    'supplier_name' => $row->supplier_name,
                    'stock_at_location' => $row->stock_at_location !== null ? (float) $row->stock_at_location : null,
                    'selling_price' => $this->resolveProductSearchPrice($row, $saleChannel, $customerGroupId),
                    'unit_name' => $row->sale_unit ?: 'PCS',
                    'has_serial_number' => (bool) $row->has_serial_number,
                    'is_open_price' => (bool) $row->is_open_price,
                    'image_url' => $row->primaryImageUrl(),
                ];
            })->values()->all(),
        ]);
    }

    public function searchCustomers(Request $request)
    {
        if (! Schema::hasTable('customers')) {
            return response()->json(['results' => []]);
        }

        $validated = $request->validate([
            'q' => 'nullable|string|max:80',
        ]);

        $q = trim((string) ($validated['q'] ?? ''));

        $rows = Customer::query()
            ->select(['id', 'name', 'phone', 'email', 'type', 'is_active'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                        ->orWhere('phone', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'results' => $rows->map(function (Customer $customer) {
                $suffix = $customer->type === 'member' ? 'Member' : 'Biasa';
                $contact = $customer->phone ?: $customer->email;
                $text = trim($customer->name . ' (' . $suffix . ($contact ? ' - ' . $contact : '') . ')');

                return [
                    'id' => $customer->id,
                    'text' => $text,
                ];
            })->values()->all(),
        ]);
    }

    private function memberPointSpendAmount(): int
    {
        return max(1, PosSetting::getInt(self::SETTING_MEMBER_POINT_SPEND, 50000));
    }

    private function memberPointResetEvery(): int
    {
        if (! Schema::hasTable('pos_settings')) {
            return 0;
        }

        return max(0, PosSetting::getInt(self::SETTING_MEMBER_POINT_RESET_EVERY, 0));
    }

    private function memberPointResetUnit(): string
    {
        if (! Schema::hasTable('pos_settings')) {
            return 'month';
        }

        $unit = PosSetting::getString(self::SETTING_MEMBER_POINT_RESET_UNIT, 'month');
        return in_array($unit, ['month', 'year'], true) ? $unit : 'month';
    }

    private function calculateMemberPoints(float $grandTotal): int
    {
        $divider = $this->memberPointSpendAmount();
        if ($divider <= 0) {
            return 0;
        }

        return (int) floor($grandTotal / $divider);
    }

    private function ensureMemberPointsReset(): void
    {
        if (! Schema::hasTable('pos_settings')) {
            return;
        }

        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'points_balance')) {
            return;
        }

        $every = $this->memberPointResetEvery();
        if ($every <= 0) {
            return; // disabled
        }

        $unit = $this->memberPointResetUnit();
        $lastResetAt = PosSetting::getString(self::SETTING_MEMBER_POINT_LAST_RESET_AT, null);
        if (! $lastResetAt) {
            PosSetting::setString(self::SETTING_MEMBER_POINT_LAST_RESET_AT, now()->toDateTimeString());
            return;
        }

        try {
            $last = \Illuminate\Support\Carbon::parse($lastResetAt);
        } catch (\Throwable $e) {
            PosSetting::setString(self::SETTING_MEMBER_POINT_LAST_RESET_AT, now()->toDateTimeString());
            return;
        }

        $next = $unit === 'year' ? $last->copy()->addYears($every) : $last->copy()->addMonths($every);
        if (now()->lt($next)) {
            return;
        }

        // Perform reset once. If server is long-running, this will happen at first request after due.
        DB::transaction(function () use ($next) {
            if (Schema::hasTable('customer_point_ledgers')) {
                Customer::query()
                    ->where('type', 'member')
                    ->where('points_balance', '>', 0)
                    ->orderBy('id')
                    ->chunkById(200, function ($customers) {
                        foreach ($customers as $customer) {
                            $old = (int) ($customer->points_balance ?? 0);
                            if ($old <= 0) {
                                continue;
                            }

                            CustomerPointLedger::create([
                                'customer_id' => $customer->id,
                                'sale_id' => null,
                                'points' => -$old,
                                'balance_after' => 0,
                                'source' => 'reset',
                                'reference_code' => 'RESET-' . now()->format('Ym'),
                                'notes' => 'Reset poin otomatis.',
                                'created_by' => auth()->id(),
                            ]);
                        }
                    });
            }

            Customer::query()
                ->where('type', 'member')
                ->update(['points_balance' => 0]);
        });

        PosSetting::setString(self::SETTING_MEMBER_POINT_LAST_RESET_AT, now()->toDateTimeString());
    }

    private function generateSaleCode(): string
    {
        $prefix = PrinterSettingController::referencePrefix('store_sale', 'TRX');

        do {
            $code = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while (Schema::hasTable('sales') && Sale::where('sale_code', $code)->exists());

        return $code;
    }

    private function normalizeSaleChannel(?string $channel): string
    {
        return in_array($channel, ['toko', 'cabang', 'partai'], true) ? $channel : 'toko';
    }

    private function saleChannelLabel(string $channel): string
    {
        return match ($this->normalizeSaleChannel($channel)) {
            'cabang' => 'Penjualan Cabang',
            'partai' => 'Penjualan Partai',
            default => 'Penjualan Toko',
        };
    }

    private function saleChannelPriceColumn(string $channel): string
    {
        return match ($this->normalizeSaleChannel($channel)) {
            'cabang' => 'price_cabang',
            'partai' => 'price_partai',
            default => 'price_toko',
        };
    }

    private function resolveProductUnitPrice(Product $product, ?ProductUnit $unit, string $channel, ?int $customerGroupId = null): float
    {
        if ($customerGroupId !== null) {
            $groupPrice = \App\Models\ProductCustomerGroupPrice::query()
                ->where('product_id', $product->id)
                ->where('customer_group_id', $customerGroupId)
                ->where('channel', $channel)
                ->value('price');

            if ($groupPrice !== null && (float) $groupPrice > 0) {
                if ($unit && $unit->level > 1 && (float) $unit->conversion_qty > 0) {
                    return (float) $groupPrice * (float) $unit->conversion_qty;
                }
                return (float) $groupPrice;
            }
        }

        if (! $unit) {
            return (float) $product->selling_price;
        }

        $column = $this->saleChannelPriceColumn($channel);
        $channelPrice = $unit->{$column} ?? null;

        if ($channelPrice !== null && (float) $channelPrice > 0) {
            return (float) $channelPrice;
        }

        if ($unit->price_toko !== null && (float) $unit->price_toko > 0) {
            return (float) $unit->price_toko;
        }

        return (float) $product->selling_price;
    }

    private function resolveProductSearchPrice(Product $product, string $channel, ?int $customerGroupId = null): float
    {
        $unit = $product->relationLoaded('units')
            ? $product->units->firstWhere('level', 1)
            : null;

        return $this->resolveProductUnitPrice($product, $unit, $channel, $customerGroupId);
    }

    private function applySaleChannelFilter($query, string $channel): void
    {
        if (! Schema::hasTable('sales') || ! Schema::hasColumn('sales', 'sale_channel')) {
            return;
        }

        $channel = $this->normalizeSaleChannel($channel);
        if ($channel === 'toko') {
            $query->where(function ($query) {
                $query->where('sale_channel', 'toko')
                    ->orWhereNull('sale_channel');
            });
            return;
        }

        $query->where('sale_channel', $channel);
    }

    private function formatItemsSummary(Sale $sale): string
    {
        $items = $sale->items ?? collect();
        $first = $items->first();

        if (! $first) {
            return '<span class="text-muted">-</span>';
        }

        $formatItem = function ($item, bool $muted = false): string {
            $quantity = rtrim(rtrim(number_format((float) $item->quantity, 2, ',', '.'), '0'), ',');
            $unit = $item->unit_name ?: 'PCS';
            $subtotal = 'Rp ' . number_format((float) $item->subtotal, 0, ',', '.');
            $nameClass = $muted ? 'text-body' : 'fw-semibold text-body';

            return '<div class="' . $nameClass . '">' . e($item->product_name ?: '-') . '</div>'
                . '<div class="text-muted small">'
                . e($item->product_code ?: '-')
                . ' | ' . e($quantity . ' ' . $unit)
                . ' | ' . e($subtotal)
                . '</div>';
        };

        $html = '<div class="trx-item-summary">' . $formatItem($first) . '</div>';
        $remaining = $items->slice(1)->values();

        if ($remaining->isNotEmpty()) {
            $collapseId = 'sale-items-' . $sale->id;
            $html .= '<button type="button" class="btn btn-xs btn-outline-secondary mt-1" data-bs-toggle="collapse" data-bs-target="#' . e($collapseId) . '" aria-expanded="false" aria-controls="' . e($collapseId) . '">'
                . '+' . $remaining->count() . ' item'
                . '</button>';
            $html .= '<div class="collapse mt-2" id="' . e($collapseId) . '">';
            foreach ($remaining as $item) {
                $html .= '<div class="border-top pt-2 mt-2">' . $formatItem($item, true) . '</div>';
            }
            $html .= '</div>';
        }

        return $html;
    }
}

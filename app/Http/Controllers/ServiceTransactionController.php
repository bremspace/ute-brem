<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceTransaction;
use App\Models\ServiceTransactionItem;
use App\Models\TransactionPayment;
use App\Models\User;
use App\Services\BackOfficeCashService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ServiceTransactionController extends Controller
{
    public function __construct(
        private readonly StockLedgerService $stockLedgerService,
        private readonly BackOfficeCashService $backOfficeCashService
    ) {
    }

    public function index()
    {
        $recentTransactions = Schema::hasTable('service_transactions')
            ? ServiceTransaction::query()
                ->with(['cashier:id,name', 'technician:id,name'])
                ->latest('service_at')
                ->limit(30)
                ->get()
            : collect();

        return view('service-transactions.index', [
            'tableReady' => Schema::hasTable('service_transactions'),
            'recentTransactions' => $recentTransactions,
        ]);
    }

    public function getData()
    {
        if (! Schema::hasTable('service_transactions')) {
            return response()->json(['draw' => (int) request('draw'), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        }

        $rows = ServiceTransaction::query()
            ->with(['cashier:id,name', 'technician:id,name'])
            ->select(['id', 'service_code', 'service_at', 'customer_name', 'device_brand', 'device_type', 'grand_total', 'status', 'cashier_id', 'technician_id']);

        return datatables()->of($rows)
            ->addColumn('code_link', fn (ServiceTransaction $row) => '<a href="' . route('service-transactions.show', $row) . '" class="fw-semibold text-primary text-decoration-none">' . e($row->service_code) . '</a>')
            ->addColumn('date_label', fn (ServiceTransaction $row) => optional($row->service_at)->format('d M Y H:i') ?: '-')
            ->addColumn('device_label', fn (ServiceTransaction $row) => trim(($row->device_brand ?: '-') . ' ' . ($row->device_type ?: '')))
            ->addColumn('technician_label', fn (ServiceTransaction $row) => $row->technician?->name ?: '-')
            ->addColumn('cashier_label', fn (ServiceTransaction $row) => $row->cashier?->name ?: '-')
            ->addColumn('grand_total_label', fn (ServiceTransaction $row) => 'Rp ' . number_format((float) $row->grand_total, 0, ',', '.'))
            ->addColumn('status_badge', function (ServiceTransaction $row) {
                $colors = ['process' => 'warning', 'done' => 'info', 'taken' => 'success', 'cancelled' => 'secondary'];
                $labels = ['process' => 'Proses', 'done' => 'Selesai', 'taken' => 'Diambil', 'cancelled' => 'Batal'];
                return '<span class="badge bg-label-' . ($colors[$row->status] ?? 'secondary') . '">' . ($labels[$row->status] ?? $row->status) . '</span>';
            })
            ->addColumn('action', fn (ServiceTransaction $row) => '<a href="' . route('service-transactions.show', $row) . '" class="btn btn-sm btn-outline-primary"><i class="bx bx-show me-1"></i>View</a>')
            ->rawColumns(['code_link', 'status_badge', 'action'])
            ->make(true);
    }

    public function create()
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('service-transactions.create', [
            'tableReady' => Schema::hasTable('service_transactions') && Schema::hasTable('services'),
            'customersTableReady' => Schema::hasTable('customers'),
            'locations' => $locations,
            'defaultLocationId' => $locations->first()?->id,
            'technicians' => User::orderBy('name')->get(['id', 'name']),
            'draftServiceCode' => $this->generateServiceTransactionCode(),
        ]);
    }

    public function store(Request $request)
    {
        if (! Schema::hasTable('service_transactions') || ! Schema::hasTable('services')) {
            return redirect()->route('service-transactions.index')->with('error', 'Fitur transaksi service belum siap. Jalankan migration terlebih dahulu.');
        }

        $validated = $request->validate([
            'service_code' => ['required', 'string', 'max:80', Rule::unique('service_transactions', 'service_code')],
            'service_at' => 'required|date',
            'return_date' => 'nullable|date',
            'customer_id' => Schema::hasTable('customers') ? 'nullable|exists:customers,id' : 'nullable',
            'customer_code' => 'nullable|string|max:80',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'nullable|string|max:80',
            'customer_address' => 'nullable|string|max:1000',
            'device_brand' => 'nullable|string|max:120',
            'device_type' => 'nullable|string|max:120',
            'serial_number' => 'nullable|string|max:160',
            'device_lock_type' => ['nullable', Rule::in(['pin', 'pattern'])],
            'device_lock_value' => 'nullable|string|max:255',
            'technician_id' => 'nullable|exists:users,id',
            'technician_commission' => 'nullable|numeric|min:0',
            'check_notes' => 'nullable|string|max:1000',
            'complaint' => 'nullable|string|max:2000',
            'accessories' => 'nullable|string|max:2000',
            'location_id' => 'required|exists:locations,id',
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris', 'tempo'])],
            'payment_reference' => 'nullable|string|max:120',
            'credit_term_days' => 'nullable|integer|min:1|max:3650',
            'paid_amount' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(['process', 'done', 'taken', 'cancelled'])],
            'is_checked' => 'nullable|boolean',
            'print_detail_price' => 'nullable|boolean',
            'invoice_format' => 'nullable|string|max:50',
            'services' => 'nullable|array',
            'services.*.service_id' => 'required_with:services|exists:services,id',
            'services.*.quantity' => 'required_with:services|numeric|min:0.01',
            'services.*.unit_price' => 'required_with:services|numeric|min:0',
            'services.*.discount_value' => 'nullable|numeric|min:0',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'required_with:products|numeric|min:0.01',
            'products.*.unit_price' => 'required_with:products|numeric|min:0',
            'products.*.discount_value' => 'nullable|numeric|min:0',
        ]);

        $location = Location::where('is_active', true)->findOrFail($validated['location_id']);
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
                return back()->withInput()->with('error', 'Masukkan kas awal dulu sebelum transaksi service.');
            }
        }

        $serviceRows = collect($validated['services'] ?? [])->filter(fn ($row) => ! empty($row['service_id']))->values();
        $productRows = collect($validated['products'] ?? [])->filter(fn ($row) => ! empty($row['product_id']))->values();

        if ($serviceRows->isEmpty() && $productRows->isEmpty()) {
            return back()->withInput()->with('error', 'Minimal pilih satu jasa atau sparepart.');
        }

        $services = Service::whereIn('id', $serviceRows->pluck('service_id')->all())->get()->keyBy('id');
        $products = Product::whereIn('id', $productRows->pluck('product_id')->all())->get()->keyBy('id');

        $items = [];
        $subtotal = 0.0;
        $discountTotal = 0.0;

        foreach ($serviceRows as $row) {
            $service = $services->get((int) $row['service_id']);
            if (! $service) {
                continue;
            }
            $qty = (float) $row['quantity'];
            $price = $service->is_open_price ? (float) $row['unit_price'] : (float) $service->price_toko;
            $discount = $service->allow_discount_override ? (float) ($row['discount_value'] ?? 0) : 0.0;
            $line = max(0, ($qty * $price) - $discount);
            $subtotal += $qty * $price;
            $discountTotal += $discount;
            $items[] = [
                'item_type' => 'service',
                'service_id' => $service->id,
                'code' => $service->service_code,
                'name' => $service->name,
                'quantity' => $qty,
                'unit_price' => $price,
                'purchase_price' => 0.00,
                'discount_value' => $discount,
                'subtotal' => $line,
                'is_open_price' => $service->is_open_price,
                'allow_discount_override' => $service->allow_discount_override,
            ];
        }

        foreach ($productRows as $row) {
            $product = $products->get((int) $row['product_id']);
            if (! $product) {
                continue;
            }
            $qty = (float) $row['quantity'];
            $price = $product->is_open_price ? (float) $row['unit_price'] : (float) $product->selling_price;
            $discount = $product->allow_discount_override ? (float) ($row['discount_value'] ?? 0) : 0.0;
            $line = max(0, ($qty * $price) - $discount);
            $subtotal += $qty * $price;
            $discountTotal += $discount;
            $items[] = [
                'item_type' => 'product',
                'product_id' => $product->id,
                'code' => $product->product_code,
                'name' => $product->name,
                'quantity' => $qty,
                'unit_price' => $price,
                'purchase_price' => (float) ($product->purchase_price ?: 0),
                'discount_value' => $discount,
                'subtotal' => $line,
                'is_open_price' => $product->is_open_price,
                'allow_discount_override' => $product->allow_discount_override,
            ];
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
            return back()->withInput()->with('error', 'Jumlah bayar kurang dari total service.');
        }

        if ($isTempo && $paidAmount > $grandTotal) {
            return back()->withInput()->with('error', 'Bayar awal tempo tidak boleh lebih besar dari grand total.');
        }

        $creditTermDays = $isTempo ? (int) $validated['credit_term_days'] : null;
        $creditDueAt = $isTempo ? Carbon::parse($validated['service_at'])->addDays($creditTermDays)->toDateString() : null;
        $creditStatus = ! $isTempo
            ? 'paid'
            : ($paidAmount >= $grandTotal ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'));

        $transaction = DB::transaction(function () use ($validated, $customer, $location, $cashSession, $items, $grandTotal, $subtotal, $discountTotal, $paidAmount, $creditTermDays, $creditDueAt, $creditStatus) {
            $transaction = ServiceTransaction::create([
                'service_code' => $validated['service_code'],
                'service_at' => $validated['service_at'],
                'return_date' => $validated['return_date'] ?? null,
                'customer_id' => $customer?->id,
                'customer_code' => $validated['customer_code'] ?? null,
                'customer_name' => $validated['customer_name'],
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_address' => $validated['customer_address'] ?? null,
                'device_brand' => $validated['device_brand'] ?? null,
                'device_type' => $validated['device_type'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'device_lock_type' => $validated['device_lock_type'] ?? null,
                'device_lock_value' => $validated['device_lock_value'] ?? null,
                'technician_id' => $validated['technician_id'] ?? null,
                'technician_commission' => (float) ($validated['technician_commission'] ?? 0),
                'check_notes' => $validated['check_notes'] ?? null,
                'complaint' => $validated['complaint'] ?? null,
                'accessories' => $validated['accessories'] ?? null,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'change_amount' => $validated['payment_method'] === 'tempo' ? 0 : ($paidAmount - $grandTotal),
                'location_id' => $location->id,
                'cashier_id' => auth()->id(),
                'cash_session_id' => $cashSession?->id,
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'] ?? null,
                'credit_term_days' => $creditTermDays,
                'credit_due_at' => $creditDueAt,
                'credit_status' => $creditStatus,
                'status' => $validated['status'],
                'is_checked' => (bool) ($validated['is_checked'] ?? false),
                'print_detail_price' => (bool) ($validated['print_detail_price'] ?? false),
                'invoice_format' => $validated['invoice_format'] ?? 'standart',
            ]);

            foreach ($items as $item) {
                ServiceTransactionItem::create(array_merge($item, ['service_transaction_id' => $transaction->id]));
            }

            foreach ($transaction->items()->where('item_type', 'product')->with('product')->get() as $item) {
                if ($item->product) {
                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $location,
                        StockLedgerService::TYPE_OUT,
                        (float) $item->quantity,
                        'Sparepart service ' . $transaction->service_code,
                        $transaction->service_at,
                        'service',
                        $transaction->service_code
                    );
                }
            }

            // Record cash inflow to backoffice cash accounts
            $cashAmount = ($transaction->payment_method === 'tempo') ? (float) $transaction->paid_amount : (float) $transaction->grand_total;
            if ($cashAmount > 0) {
                $this->backOfficeCashService->recordPOSInflow(
                    $cashAmount,
                    $transaction->payment_method,
                    $transaction->service_code,
                    $transaction->customer,
                    'Pendapatan Servis ' . $transaction->service_code,
                    Carbon::parse($transaction->service_at)->toDateString()
                );
            }

            return $transaction->fresh(['items', 'cashier', 'technician', 'location']);
        });

        return redirect()->route('service-transactions.show', $transaction)->with('success', 'Transaksi service berhasil disimpan.');
    }

    public function show(ServiceTransaction $serviceTransaction)
    {
        $serviceTransaction->load(['items.service', 'items.product', 'cashier', 'technician', 'location', 'customer', 'payments.receiver']);

        return view('service-transactions.show', compact('serviceTransaction'));
    }

    public function storePayment(Request $request, ServiceTransaction $serviceTransaction)
    {
        if ($serviceTransaction->status === 'cancelled') {
            return back()->with('error', 'Transaksi service batal tidak bisa menerima pelunasan.');
        }

        if (! $serviceTransaction->customer_id) {
            return back()->with('error', 'Pelunasan tempo hanya bisa untuk transaksi dengan customer terdaftar.');
        }

        $validated = $request->validate([
            'payment_at' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris'])],
            'reference' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $outstanding = max(0, (float) $serviceTransaction->grand_total - (float) $serviceTransaction->paid_amount);
        $amount = (float) $validated['amount'];

        if ($outstanding <= 0) {
            return back()->with('error', 'Transaksi service ini sudah lunas.');
        }

        if ($amount > $outstanding) {
            return back()->withInput()->with('error', 'Nominal pelunasan lebih besar dari sisa tempo.');
        }

        DB::transaction(function () use ($serviceTransaction, $validated, $amount) {
            TransactionPayment::create([
                'sale_id' => null,
                'service_transaction_id' => $serviceTransaction->id,
                'customer_id' => $serviceTransaction->customer_id,
                'payment_at' => $validated['payment_at'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
            ]);

            $paid = (float) $serviceTransaction->paid_amount + $amount;
            $serviceTransaction->update([
                'paid_amount' => $paid,
                'change_amount' => 0,
                'credit_status' => $paid >= (float) $serviceTransaction->grand_total ? 'paid' : 'partial',
            ]);

            $serviceTransaction->loadMissing('customer');
            $this->backOfficeCashService->recordPaymentInflow(
                $amount,
                $validated['payment_method'],
                $serviceTransaction->service_code,
                $serviceTransaction->customer,
                'Pelunasan tempo Servis ' . $serviceTransaction->service_code,
                Carbon::parse($validated['payment_at'])->toDateString()
            );
        });

        return back()->with('success', 'Pelunasan tempo service berhasil dicatat.');
    }

    public function void(Request $request, ServiceTransaction $serviceTransaction)
    {
        $validated = $request->validate([
            'void_reason' => 'nullable|string|max:500',
        ]);

        if ($serviceTransaction->status === 'cancelled') {
            return back()->with('error', 'Transaksi service sudah dibatalkan.');
        }

        $serviceTransaction->load(['items.product', 'location']);

        if (! $serviceTransaction->location) {
            return back()->with('error', 'Lokasi transaksi service tidak ditemukan, stok tidak bisa dikembalikan.');
        }

        try {
            DB::transaction(function () use ($serviceTransaction, $validated) {
                foreach ($serviceTransaction->items as $item) {
                    if ($item->item_type !== 'product' || ! $item->product) {
                        continue;
                    }

                    $this->stockLedgerService->applyMovement(
                        $item->product,
                        $serviceTransaction->location,
                        StockLedgerService::TYPE_IN,
                        (float) $item->quantity,
                        'Void transaksi service ' . $serviceTransaction->service_code,
                        now(),
                        'service_void',
                        $serviceTransaction->service_code
                    );
                }

                $reason = trim((string) ($validated['void_reason'] ?? ''));
                $checkNotes = trim((string) ($serviceTransaction->check_notes ?? ''));
                $voidNote = 'VOID ' . now()->format('d/m/Y H:i') . ($reason !== '' ? ': ' . $reason : '');

                $serviceTransaction->update([
                    'status' => 'cancelled',
                    'check_notes' => trim($checkNotes . "\n" . $voidNote),
                ]);

                // Reverse cash inflow to backoffice cash accounts
                $cashAmount = ($serviceTransaction->payment_method === 'tempo') ? (float) $serviceTransaction->paid_amount : (float) $serviceTransaction->grand_total;
                if ($cashAmount > 0) {
                    $this->backOfficeCashService->reversePOSInflow(
                        $cashAmount,
                        $serviceTransaction->payment_method,
                        $serviceTransaction->service_code,
                        $serviceTransaction->customer,
                        'Void transaksi servis ' . $serviceTransaction->service_code
                    );
                }

                // Reverse any subsequent installment payments
                if ($serviceTransaction->payment_method === 'tempo') {
                    $serviceTransaction->loadMissing('payments');
                    foreach ($serviceTransaction->payments as $payment) {
                        $this->backOfficeCashService->reversePOSInflow(
                            (float) $payment->amount,
                            $payment->payment_method,
                            $serviceTransaction->service_code,
                            $serviceTransaction->customer,
                            'Void pelunasan tempo servis ' . $serviceTransaction->service_code
                        );
                    }
                }
            });
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('service-transactions.show', $serviceTransaction)->with('success', 'Transaksi service berhasil di-void dan stok sparepart sudah dikembalikan.');
    }

    private function generateServiceTransactionCode(): string
    {
        $prefix = PrinterSettingController::referencePrefix('store_sale', 'SRV');

        do {
            $code = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while (Schema::hasTable('service_transactions') && ServiceTransaction::where('service_code', $code)->exists());

        return $code;
    }
}

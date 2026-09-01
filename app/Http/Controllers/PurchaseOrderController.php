<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\UserLog;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private readonly StockLedgerService $stockLedgerService,
        private readonly \App\Services\BackOfficeCashService $backOfficeCashService
    ) {
    }

    public function index()
    {
        return view('purchase-orders.index');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'product:id,product_code,name,sale_unit,stock_global,stock_min',
            'supplier:id,name,code,phone,email,contact_person,address',
            'location:id,name,code',
            'stockMovement',
            'creator:id,name',
            'files',
            'payments.creator:id,name',
        ]);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function create(Request $request)
    {
        $product = Product::with(['productSuppliers.supplier', 'defaultLocation', 'stocks.location'])
            ->findOrFail($request->integer('product_id'));

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Location::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedLocationId = $request->integer('location_id') ?: $product->default_location_id;
        $selectedStock = $selectedLocationId
            ? $product->stocks->firstWhere('location_id', $selectedLocationId)
            : null;
        $primarySupplier = $product->productSuppliers->firstWhere('is_primary', true)
            ?: $product->productSuppliers->first();

        $suggestedQty = $selectedStock?->stock_min !== null
            ? max(1, (float) $selectedStock->stock_min - (float) $selectedStock->quantity)
            : max(1, (float) ($product->stock_min ?? 0) - (float) ($product->stock_global ?? 0));

        return view('purchase-orders.create', [
            'product' => $product,
            'suppliers' => $suppliers,
            'locations' => $locations,
            'primarySupplier' => $primarySupplier,
            'selectedLocationId' => $selectedLocationId,
            'suggestedQty' => $suggestedQty,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'location_id' => 'required|exists:locations,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'ordered_at' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'return_to' => ['nullable', Rule::in(['stock', 'products'])],
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,webp,pdf,xls,xlsx,doc,docx',
            'payment_method' => ['required', Rule::in(['cash', 'tempo'])],
            'credit_term_days' => 'nullable|integer|min:1',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $supplier = Supplier::findOrFail($validated['supplier_id']);
        $location = Location::findOrFail($validated['location_id']);
        $quantity = (float) $validated['quantity'];
        $unitPrice = $validated['unit_price'] !== null ? (float) $validated['unit_price'] : 0.0;
        $totalPrice = $unitPrice * $quantity;

        $paymentMethod = $validated['payment_method'];
        $paidAmount = $paymentMethod === 'cash' ? $totalPrice : (float) ($validated['paid_amount'] ?? 0);
        $creditTermDays = $paymentMethod === 'tempo' ? ($validated['credit_term_days'] ?? null) : null;
        $creditDueAt = null;
        if ($paymentMethod === 'tempo' && $creditTermDays) {
            $creditDueAt = \Carbon\Carbon::parse($validated['ordered_at'])->addDays((int) $creditTermDays)->toDateString();
        }

        $creditStatus = 'paid';
        if ($paymentMethod === 'tempo') {
            if ($paidAmount >= $totalPrice) {
                $creditStatus = 'paid';
            } elseif ($paidAmount > 0) {
                $creditStatus = 'partial';
            } else {
                $creditStatus = 'tempo';
            }
        }

        $purchaseOrder = DB::transaction(function () use ($request, $validated, $product, $supplier, $location, $quantity, $unitPrice, $totalPrice, $paymentMethod, $creditTermDays, $creditDueAt, $creditStatus, $paidAmount) {
            $po = PurchaseOrder::create([
                'po_number' => $this->generatePoNumber(),
                'product_id' => $product->id,
                'supplier_id' => $supplier->id,
                'location_id' => $location->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'payment_method' => $paymentMethod,
                'credit_term_days' => $creditTermDays,
                'credit_due_at' => $creditDueAt,
                'credit_status' => $creditStatus,
                'paid_amount' => $paidAmount,
                'status' => 'received',
                'notes' => $validated['notes'] ?? null,
                'ordered_at' => $validated['ordered_at'],
                'received_at' => $validated['ordered_at'],
                'created_by' => auth()->id(),
            ]);

            $movement = $this->stockLedgerService->applyMovement(
                $product,
                $location,
                StockLedgerService::TYPE_IN,
                $quantity,
                'PO ' . $po->po_number . ' dari ' . $supplier->name . ($po->notes ? ' - ' . $po->notes : ''),
                $validated['ordered_at'],
                'purchase_order',
                $po->po_number
            );

            $po->update(['stock_movement_id' => $movement->id]);

            // Record cash outflow to backoffice cash accounts
            $outflowAmount = $paidAmount;
            if ($outflowAmount > 0) {
                $this->backOfficeCashService->recordPOOutflow(
                    $outflowAmount,
                    $paymentMethod,
                    $po->po_number,
                    $supplier,
                    'Pembelian PO ' . $po->po_number,
                    \Carbon\Carbon::parse($po->ordered_at)->toDateString()
                );
            }

            // Update buying price records on product using Moving Average HPP
            $currentHpp = (float) ($product->purchase_price ?: 0);
            $currentStock = (float) ($product->stock_global ?: 0);
            $newHpp = $currentHpp;
            if ($currentStock + $quantity > 0) {
                $newHpp = (($currentStock * $currentHpp) + ($quantity * $unitPrice)) / ($currentStock + $quantity);
            }

            if ($newHpp > 0) {
                $product->update(['purchase_price' => $newHpp]);
            }

            // Always update last purchase price on product_suppliers relation with actual PO price
            if ($unitPrice > 0) {
                \App\Models\ProductSupplier::query()->updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'supplier_id' => $supplier->id,
                    ],
                    [
                        'last_purchase_price' => $unitPrice,
                    ]
                );
            }

            $this->storeAttachments($request, $po);

            return $po;
        });

        UserLog::log(
            'CREATE_PURCHASE_ORDER',
            "Created purchase order {$purchaseOrder->po_number} for product: {$product->name}",
            null,
            null,
            $purchaseOrder->only(['po_number', 'product_id', 'supplier_id', 'location_id', 'quantity', 'unit_price', 'total_price'])
        );

        return redirect()
            ->route('products.stocks.index', $product)
            ->with('success', "Purchase order {$purchaseOrder->po_number} berhasil dibuat dan stok masuk sudah dicatat.");
    }

    public function getData()
    {
        $rows = PurchaseOrder::query()
            ->with(['product:id,product_code,name,sale_unit', 'supplier:id,name', 'location:id,name', 'files'])
            ->withCount('files')
            ->select([
                'id',
                'po_number',
                'product_id',
                'supplier_id',
                'location_id',
                'quantity',
                'unit_price',
                'total_price',
                'status',
                'ordered_at',
                'created_at',
            ]);

        return datatables()->of($rows)
            ->addColumn('po_info', function ($row) {
                return '<a href="' . route('purchase-orders.show', $row->id) . '" class="fw-semibold text-primary">' . e($row->po_number) . '</a>';
            })
            ->addColumn('ordered_at_label', function ($row) {
                return optional($row->ordered_at)->format('d M Y H:i') ?: '-';
            })
            ->addColumn('product_info', function ($row) {
                return '<div class="fw-semibold">' . e($row->product?->name ?: '-') . '</div>';
            })
            ->addColumn('supplier_name', fn ($row) => $row->supplier?->name ?: '-')
            ->addColumn('location_name', fn ($row) => $row->location?->name ?: '-')
            ->addColumn('qty_label', function ($row) {
                $qty = rtrim(rtrim(number_format((float) $row->quantity, 2, ',', '.'), '0'), ',');
                $unit = $row->product?->sale_unit ?: 'PCS';

                return $qty . ' ' . e($unit);
            })
            ->addColumn('total_label', function ($row) {
                return $row->total_price !== null
                    ? 'Rp ' . number_format((float) $row->total_price, 0, ',', '.')
                    : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $variant = $row->status === 'received' ? 'success' : 'secondary';
                $label = $row->status === 'received' ? 'Diterima' : ucfirst((string) $row->status);

                return '<span class="badge bg-label-' . $variant . '">' . e($label) . '</span>';
            })
            ->addColumn('attachments', function ($row) {
                if ((int) $row->files_count === 0) {
                    return '<span class="text-muted">-</span>';
                }

                return '<a href="' . route('purchase-orders.show', $row->id) . '" class="badge bg-label-primary">'
                    . '<i class="bx bx-paperclip me-1"></i>' . (int) $row->files_count . ' file</a>';
            })
            ->addColumn('action', function ($row) {
                $actions = '<div class="dropdown"><button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button><div class="dropdown-menu">';
                $actions .= '<a class="dropdown-item" href="' . route('purchase-orders.show', $row->id) . '"><i class="bx bx-show me-1"></i>View Ringkasan</a>';

                if ($row->product_id) {
                    $actions .= '<a class="dropdown-item" href="' . route('products.stocks.index', $row->product_id) . '"><i class="bx bx-box me-1"></i>Stok Lokasi</a>';
                }

                return $actions . '</div></div>';
            })
            ->rawColumns(['po_info', 'product_info', 'status_badge', 'attachments', 'action'])
            ->make(true);
    }

    private function generatePoNumber(): string
    {
        $prefix = PrinterSettingController::referencePrefix('purchase', 'PO');

        do {
            $number = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }

    private function storeAttachments(Request $request, PurchaseOrder $purchaseOrder): void
    {
        if (! $request->hasFile('attachments')) {
            return;
        }

        foreach ($request->file('attachments') as $file) {
            if (! $file->isValid()) {
                continue;
            }

            $path = $file->store('purchase-orders/' . $purchaseOrder->po_number, 'public');

            $purchaseOrder->files()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    public function storePayment(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->payment_method !== 'tempo') {
            return back()->with('error', 'Transaksi PO ini tidak menggunakan metode tempo.');
        }

        $validated = $request->validate([
            'payment_at' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'qris'])],
            'reference' => 'nullable|string|max:120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $purchaseOrder->load('payments');
        $outstanding = max(0, (float) $purchaseOrder->total_price - (float) $purchaseOrder->paid_amount);
        $amount = (float) $validated['amount'];

        if ($outstanding <= 0) {
            return back()->with('error', 'Hutang PO ini sudah lunas.');
        }

        if ($amount > $outstanding) {
            return back()->withInput()->with('error', 'Nominal pelunasan lebih besar dari sisa hutang.');
        }

        DB::transaction(function () use ($purchaseOrder, $validated, $amount) {
            \App\Models\PurchaseOrderPayment::create([
                'purchase_order_id' => $purchaseOrder->id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'payment_at' => $validated['payment_at'],
                'amount' => $amount,
                'payment_method' => $validated['payment_method'],
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $paid = (float) $purchaseOrder->paid_amount + $amount;
            $purchaseOrder->update([
                'paid_amount' => $paid,
                'credit_status' => $paid >= (float) $purchaseOrder->total_price ? 'paid' : 'partial',
            ]);

            $purchaseOrder->loadMissing('supplier');
            $this->backOfficeCashService->recordSupplierPaymentOutflow(
                $amount,
                $validated['payment_method'],
                $purchaseOrder->po_number,
                $purchaseOrder->supplier,
                'Pelunasan hutang PO ' . $purchaseOrder->po_number,
                \Carbon\Carbon::parse($validated['payment_at'])->toDateString()
            );
        });

        return back()->with('success', 'Pelunasan hutang supplier berhasil dicatat.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\BackOfficeStockDocument;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Services\AccountingPostingService;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function __construct(
        private readonly StockLedgerService $stockLedgerService,
        private readonly AccountingPostingService $accountingPostingService
    ) {
    }

    public function index()
    {
        $opnames = StockOpname::with(['location', 'creator'])->orderByDesc('id')->get();
        return view('stock-opname.index', compact('opnames'));
    }

    public function create(Request $request)
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $selectedLocationId = $request->integer('location') ?: ($locations->first()?->id);
        $rows = collect();

        if ($selectedLocationId) {
            $rows = ProductStock::with('product')
                ->where('location_id', $selectedLocationId)
                ->orderBy('product_id')
                ->get()
                ->map(fn ($st) => (object) [
                    'product_id' => $st->product_id,
                    'code' => $st->product->product_code,
                    'name' => $st->product->name,
                    'system_qty' => (float) $st->quantity,
                    'hpp' => (float) $st->product->purchase_price,
                ]);
        }

        return view('stock-opname.create', compact('locations', 'selectedLocationId', 'rows'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'opname_date' => 'required|date',
            'notes' => 'nullable|string|max:255',
            'actual' => 'required|array',
            'actual.*' => 'numeric|min:0',
            'qty' => 'required|array',
        ]);

        $locationId = (int) $validated['location_id'];

        return DB::transaction(function () use ($validated, $locationId) {
            $code = 'OPN-' . date('Ymd') . '-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ123456789'), 0, 4));
            $opname = StockOpname::create([
                'opname_code' => $code,
                'location_id' => $locationId,
                'opname_date' => $validated['opname_date'],
                'status' => 'open',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $opname->items()->createMany(
                collect($validated['qty'])->map(function ($systemQty, $productId) use ($validated) {
                    $actual = (float) ($validated['actual'][$productId] ?? 0);
                    return [
                        'product_id' => (int) $productId,
                        'system_qty' => (float) $systemQty,
                        'actual_qty' => $actual,
                        'difference' => $actual - (float) $systemQty,
                    ];
                })->values()->all()
            );

            return redirect()->route('stock-opname.show', $opname)
                ->with('success', 'Stock opname dibuat. Silakan periksa selisih lalu proses.');
        });
    }

    public function show(StockOpname $opname)
    {
        $opname->load(['location', 'items.product', 'creator']);
        return view('stock-opname.show', compact('opname'));
    }

    public function complete(Request $request, StockOpname $opname)
    {
        if ($opname->status === 'completed') {
            return back()->with('info', 'Opname ini sudah diproses.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($opname) {
            $opname->load('items.product');

            foreach ($opname->items as $item) {
                $diff = (float) $item->difference;
                if (abs($diff) < 0.009) {
                    continue;
                }

                $product = $item->product ?: Product::find($item->product_id);
                $loc = $opname->location;
                $type = $diff > 0 ? StockLedgerService::TYPE_ADJUSTMENT_PLUS : StockLedgerService::TYPE_ADJUSTMENT_MINUS;

                $movement = $this->stockLedgerService->applyMovement(
                    $product, $loc, $type, abs($diff),
                    'Stock opname ' . $opname->opname_code,
                    $opname->opname_date->toDateString(),
                    'stock_opname', $opname->opname_code
                );

                BackOfficeStockDocument::create([
                    'document_code' => $opname->opname_code . '-' . $item->id,
                    'document_date' => $opname->opname_date->toDateString(),
                    'document_type' => 'correction',
                    'product_id' => $product->id,
                    'location_id' => $loc->id,
                    'movement_type' => $type,
                    'quantity' => abs($diff),
                    'description' => 'Selisih stock opname ' . $opname->opname_code,
                    'stock_movement_id' => $movement->id,
                    'created_by' => auth()->id(),
                ]);

                $this->accountingPostingService->postStockAdjustment(
                    $opname->opname_code, $item, $diff * (float) $product->purchase_price, auth()->id()
                );
            }

            $opname->update(['status' => 'completed']);
        });

        return redirect()->route('stock-opname.index')->with('success', 'Stock opname diproses: stok & pembukuan diperbarui.');
    }

    public function destroy(StockOpname $opname)
    {
        if ($opname->status === 'completed') {
            return back()->with('error', 'Opname yang sudah diproses tidak bisa dihapus.');
        }
        $opname->delete();
        return back()->with('success', 'Stock opname dihapus.');
    }
}

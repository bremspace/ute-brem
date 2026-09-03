<?php

namespace App\Http\Controllers;

use App\Models\ItemSerial;
use App\Models\Location;
use App\Models\PickingRequest;
use App\Models\PickingRequestItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PickingRequestController extends Controller
{
    public function __construct(private readonly StockLedgerService $stockLedgerService)
    {
    }

    public function index()
    {
        $requests = PickingRequest::with(['technician', 'location', 'items.product'])->orderByDesc('id')->get();
        return view('picking-requests.index', compact('requests'));
    }

    public function create()
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        // Beri info stok tiap produk agar teknisi tahu ketersediaan
        $stockMap = ProductStock::whereIn('product_id', $products->pluck('id'))
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')->pluck('total', 'product_id');

        return view('picking-requests.create', compact('locations', 'products', 'stockMap'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'technician_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        $code = 'PKG-' . date('Ymd') . '-' . strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));

        $requestRec = DB::transaction(function () use ($validated, $code) {
            $req = PickingRequest::create([
                'request_code' => $code,
                'technician_id' => $validated['technician_id'] ?? auth()->id(),
                'location_id' => $validated['location_id'],
                'status' => 'open',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $req->items()->createMany(
                collect($validated['items'])->map(fn ($it) => [
                    'product_id' => $it['product_id'],
                    'qty_requested' => $it['qty'],
                    'qty_picked' => 0,
                    'status' => 'requested',
                    'note' => $it['note'] ?? null,
                ])->values()->all()
            );

            return $req;
        });

        return redirect()->route('picking-requests.show', $requestRec)
            ->with('success', 'Picking request dibuat.');
    }

    public function show(PickingRequest $pickingRequest)
    {
        $pickingRequest->load(['technician', 'location', 'items.product', 'creator']);
        return view('picking-requests.show', compact('pickingRequest'));
    }

    public function fulfill(Request $request, PickingRequest $pickingRequest)
    {
        if ($pickingRequest->status !== 'open') {
            return back()->with('info', 'Request sudah diproses.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($pickingRequest) {
            $pickingRequest->load('items.product', 'location');

            foreach ($pickingRequest->items as $item) {
                $product = $item->product;
                $qty = (float) $item->qty_requested;
                if (! $product || $qty <= 0) {
                    continue;
                }

                // 1. Potong stok gudang di lokasi request
                $this->stockLedgerService->applyMovement(
                    $product,
                    $pickingRequest->location,
                    StockLedgerService::TYPE_OUT,
                    $qty,
                    'Picking untuk servis ' . $pickingRequest->request_code,
                    now(),
                    'picking_request',
                    $pickingRequest->request_code,
                    $item->rack
                );

                // 2. Product serialized -> tandai serial 'reserved' (Reserved for Repair)
                if ($product->has_serial_number) {
                    $available = ItemSerial::where('product_id', $product->id)
                        ->where('status', 'available')
                        ->limit($qty)
                        ->get();
                    foreach ($available as $serial) {
                        $serial->update([
                            'status' => 'reserved',
                            'reference_type' => 'picking_request',
                            'reference_code' => $pickingRequest->request_code,
                        ]);
                    }
                }

                $item->update([
                    'qty_picked' => $qty,
                    'status' => 'reserved', // Reserved for Repair
                ]);
            }

            $pickingRequest->update(['status' => 'fulfilled']);
        });

        return redirect()->route('picking-requests.show', $pickingRequest)
            ->with('success', 'Picking diproses: stok gudang terpotong & sparepart berstatus Reserved for Repair.');
    }

    public function cancel(PickingRequest $pickingRequest)
    {
        if ($pickingRequest->status === 'fulfilled') {
            return back()->with('error', 'Request yang sudah diproses tidak bisa dibatalkan.');
        }
        $pickingRequest->update(['status' => 'cancelled']);
        return back()->with('success', 'Picking request dibatalkan.');
    }

    public function destroy(PickingRequest $pickingRequest)
    {
        if ($pickingRequest->status === 'fulfilled') {
            return back()->with('error', 'Request yang sudah diproses tidak bisa dihapus.');
        }
        $pickingRequest->delete();
        return back()->with('success', 'Picking request dihapus.');
    }
}

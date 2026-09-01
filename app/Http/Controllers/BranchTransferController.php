<?php

namespace App\Http\Controllers;

use App\Models\BranchTransfer;
use App\Models\BranchTransferItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\Branch;
use App\Services\BranchTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchTransferController extends Controller
{
    public function __construct(
        private readonly BranchTransferService $branchTransferService
    ) {
    }

    public function index(Request $request)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.view'), 403);

        $query = BranchTransfer::with(['sourceBranch', 'targetBranch', 'sourceLocation', 'targetLocation', 'creator', 'recipient']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source_branch_id')) {
            $query->where('source_branch_id', $request->source_branch_id);
        }
        if ($request->filled('target_branch_id')) {
            $query->where('target_branch_id', $request->target_branch_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transfers = $query->latest()->paginate(15)->withQueryString();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        return view('branch-transfers.index', compact('transfers', 'branches'));
    }

    public function create()
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.edit'), 403);

        $branches = Branch::with([
            'locations' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
            'locations.racks' => fn ($q) => $q->where('is_active', true)->orderBy('name')
        ])->where('is_active', true)->orderBy('name')->get();

        $userBranchId = auth()->user()->branch_id;

        // Retrieve products with basic details for lookup
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'product_code', 'name']);

        return view('branch-transfers.create', compact('branches', 'userBranchId', 'products'));
    }

    public function store(Request $request)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.edit'), 403);

        $validated = $request->validate([
            'source_branch_id' => 'required|exists:branches,id',
            'target_branch_id' => 'required|exists:branches,id|different:source_branch_id',
            'source_location_id' => 'required|exists:locations,id',
            'target_location_id' => 'required|exists:locations,id|different:source_location_id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.source_location_rack_id' => 'nullable|exists:location_racks,id',
            'items.*.target_location_rack_id' => 'nullable|exists:location_racks,id',
            'items.*.quantity_sent' => 'required|numeric|min:0.01',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $transfer = BranchTransfer::create([
                    'transfer_code' => $this->branchTransferService->generateTransferCode(),
                    'source_branch_id' => $validated['source_branch_id'],
                    'target_branch_id' => $validated['target_branch_id'],
                    'source_location_id' => $validated['source_location_id'],
                    'target_location_id' => $validated['target_location_id'],
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $itemData) {
                    $transfer->items()->create([
                        'product_id' => $itemData['product_id'],
                        'source_location_rack_id' => $itemData['source_location_rack_id'] ?? null,
                        'target_location_rack_id' => $itemData['target_location_rack_id'] ?? null,
                        'quantity_sent' => $itemData['quantity_sent'],
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            });
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', 'Gagal menyimpan draf transfer: ' . $exception->getMessage());
        }

        return redirect()->route('branch-transfers.index')->with('success', 'Draf transfer stok berhasil dibuat.');
    }

    public function show(BranchTransfer $branchTransfer)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.view'), 403);

        $branchTransfer->load([
            'sourceLocation',
            'targetLocation',
            'creator',
            'recipient',
            'items.product',
            'items.sourceRack',
            'items.targetRack',
        ]);

        return view('branch-transfers.show', compact('branchTransfer'));
    }

    public function ship(BranchTransfer $branchTransfer)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.edit'), 403);

        try {
            $this->branchTransferService->ship($branchTransfer);
        } catch (\Throwable $exception) {
            return back()->with('error', 'Gagal mengirim barang: ' . $exception->getMessage());
        }

        return back()->with('success', 'Barang berhasil dikirim dan stok lokasi asal telah dipotong.');
    }

    public function receiveForm(BranchTransfer $branchTransfer)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.edit'), 403);

        if ($branchTransfer->status !== 'in_transit') {
            return redirect()->route('branch-transfers.show', $branchTransfer)
                ->with('error', 'Dokumen harus berstatus Dalam Perjalanan untuk melakukan penerimaan.');
        }

        $branchTransfer->load([
            'sourceLocation',
            'targetLocation',
            'items.product',
            'items.sourceRack',
            'items.targetRack',
            'targetLocation.racks' => fn ($q) => $q->where('is_active', true)->orderBy('name'),
        ]);

        return view('branch-transfers.receive', compact('branchTransfer'));
    }

    public function receive(Request $request, BranchTransfer $branchTransfer)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.edit'), 403);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.quantity_received' => 'required|numeric|min:0',
            'items.*.target_location_rack_id' => 'nullable|exists:location_racks,id',
        ]);

        try {
            $this->branchTransferService->receive($branchTransfer, $validated['items'], auth()->id());
        } catch (\Throwable $exception) {
            return back()->withInput()->with('error', 'Gagal memproses penerimaan barang: ' . $exception->getMessage());
        }

        return redirect()->route('branch-transfers.show', $branchTransfer)
            ->with('success', 'Penerimaan barang berhasil dikonfirmasi. Stok lokasi tujuan telah diperbarui.');
    }

    public function cancel(BranchTransfer $branchTransfer)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.edit'), 403);

        try {
            $this->branchTransferService->cancel($branchTransfer);
        } catch (\Throwable $exception) {
            return back()->with('error', 'Gagal membatalkan transfer: ' . $exception->getMessage());
        }

        return back()->with('success', 'Transfer stok berhasil dibatalkan.');
    }

    public function print(BranchTransfer $branchTransfer)
    {
        abort_if(! auth()->user()->hasPermission('master.product_stocks.view'), 403);

        $branchTransfer->load([
            'sourceLocation',
            'targetLocation',
            'creator',
            'items.product',
            'items.sourceRack',
            'items.targetRack',
        ]);

        return view('branch-transfers.print', compact('branchTransfer'));
    }
}

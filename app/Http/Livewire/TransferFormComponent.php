<?php

namespace App\Http\Livewire;

use App\Models\BranchTransfer;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Product;
use App\Models\LocationRack;
use App\Services\BranchTransferService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferFormComponent extends Component
{
    protected BranchTransferService $branchTransferService;

    public function boot(BranchTransferService $branchTransferService): void
    {
        $this->branchTransferService = $branchTransferService;
    }

    public ?BranchTransfer $transfer = null;
    public bool $isEditing = false;

    // Form state
    public string $transferCode = '';
    public string $sourceBranchId = '';
    public string $targetBranchId = '';
    public string $sourceLocationId = '';
    public string $targetLocationId = '';
    public ?int $userBranchId = null;
    public ?string $notes = null;

    // Items
    public array $items = [];

    public string $selectedProductId = '';
    public string $productCode = '';
    public string $productName = '';

    // Locations & Racks
    public array $sourceBranches = [];
    public array $targetBranches = [];
    public array $sourceLocations = [];
    public array $targetLocations = [];
    public array $racks = [];

    protected $rules = [
        'sourceBranchId' => 'required|exists:branches,id',
        'targetBranchId' => 'required|exists:branches,id|different:sourceBranchId',
        'sourceLocationId' => 'required|exists:locations,id',
        'targetLocationId' => 'required|exists:locations,id|different:sourceLocationId',
        'notes' => 'nullable|string|max:500',
        'items' => 'array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.source_location_rack_id' => 'nullable|exists:location_racks,id',
        'items.*.target_location_rack_id' => 'nullable|exists:location_racks,id',
        'items.*.quantity_sent' => 'required|numeric|min:0.01',
        'items.*.notes' => 'nullable|string|max:255',
    ];

    protected $validationAttributes = [
        'sourceBranchId' => 'Cabang Asal',
        'targetBranchId' => 'Cabang Tujuan',
        'sourceLocationId' => 'Lokasi Asal',
        'targetLocationId' => 'Lokasi Tujuan',
        'notes' => 'Catatan Pengiriman',
    ];

    public function mount(?int $transferId = null): void
    {
        // Get user branch (admin override)
        $this->userBranchId = auth()->user()->branch_id;

        // Load branches and locations
        $this->loadBranches();
        $this->loadBranchLocations();

        if ($transferId) {
            // Load existing transfer
            $this->transfer = BranchTransfer::with(['sourceLocation', 'targetLocation', 'items.product'])
                ->findOrFail($transferId);

            $this->isEditing = true;
            $this->transferCode = $this->transfer->transfer_code;

            // If editing, we're likely in receive mode (not create)
            // For now, we'll primarily focus on create flow
        }

        // Set default dates
        $this->notes = session('notes') ?? '';
        session()->forget('notes');
    }

    public function loadBranches(): void
    {
        $this->sourceBranches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($this->userBranchId) {
            $this->sourceBranchId = $this->userBranchId;
            $this->loadBranchLocations();
        }
    }

    public function loadBranchLocations(): void
    {
        $locationType = 'source';
        if ($this->sourceBranchId) {
            $this->sourceLocations = Location::where('branch_id', $this->sourceBranchId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);

            // Load racks for first location
            if (! empty($this->sourceLocations)) {
                $this->loadRacks($locationType, $this->sourceLocations[0]->id);
            }
        }

        $locationType = 'target';
        if ($this->targetBranchId) {
            $this->targetLocations = Location::where('branch_id', $this->targetBranchId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code']);

            // Load racks for first location
            if (! empty($this->targetLocations)) {
                $this->loadRacks($locationType, $this->targetLocations[0]->id);
            }
        }
    }

    public function loadRacks(string $type, int $locationId): void
    {
        $racks = LocationRack::where('location_id', $locationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($type === 'source') {
            $this->racks = $racks;
        } else {
            $this->racks = $racks;
        }
    }

    public function updatedSourceBranchId(): void
    {
        $this->sourceLocationId = '';
        $this->sourceLocations = [];
        $this->loadBranchLocations();
    }

    public function updatedTargetBranchId(): void
    {
        $this->targetLocationId = '';
        $this->targetLocations = [];
        $this->loadBranchLocations();
    }

    public function updatedSourceLocationId(): void
    {
        if ($this->sourceLocationId) {
            $this->loadRacks('source', $this->sourceLocationId);
        }
    }

    public function updatedTargetLocationId(): void
    {
        if ($this->targetLocationId) {
            $this->loadRacks('target', $this->targetLocationId);
        }
    }

    public function addProductToItems(): void
    {
        if (empty($this->selectedProductId)) {
            session()->flash('error', 'Silakan pilih produk terlebih dahulu.');
            return;
        }

        $product = Product::findOrFail($this->selectedProductId);

        if (isset($this->items[$this->selectedProductId])) {
            session()->flash('error', 'Produk tersebut sudah ditambahkan ke tabel.');
            return;
        }

        unset($this->items[$this->selectedProductId]);

        $this->items[$this->selectedProductId] = [
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->name,
            'source_location_rack_id' => '',
            'target_location_rack_id' => '',
            'quantity_sent' => '',
            'notes' => '',
        ];

        $this->selectedProductId = '';
        $this->productCode = '';
        $this->productName = '';
        $this->productCode = '';
    }

    public function removeItem(string $productId): void
    {
        unset($this->items[$productId]);
    }

    public function updateItem(string $productId, string $field, $value): void
    {
        $this->items[$productId][$field] = $value;
    }

    public function store(): void
    {
        $validated = $this->validate();

        try {
            DB::transaction(function () use ($validated) {
                $this->transfer = BranchTransfer::create([
                    'transfer_code' => $this->branchTransferService->generateTransferCode(),
                    'source_branch_id' => $validated['source_branch_id'],
                    'target_branch_id' => $validated['target_branch_id'],
                    'source_location_id' => $validated['source_location_id'],
                    'target_location_id' => $validated['target_location_id'],
                    'status' => 'draft',
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $productId => $itemData) {
                    $this->transfer->items()->create([
                        'product_id' => $itemData['product_id'],
                        'source_location_rack_id' => $itemData['source_location_rack_id'] ?? null,
                        'target_location_rack_id' => $itemData['target_location_rack_id'] ?? null,
                        'quantity_sent' => $itemData['quantity_sent'],
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            });

            session()->flash('success', 'Draf transfer stok berhasil dibuat.');
            return redirect()->route('branch-transfers.index');
        } catch (\Throwable $exception) {
            session()->flash('error', 'Gagal menyimpan draf transfer: ' . $exception->getMessage());
            throw $exception;
        }
    }

    public function render()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'product_code', 'name']);

        return view('livewire.branches.transfer-form-component', [
            'products' => $products,
        ]);
    }
}
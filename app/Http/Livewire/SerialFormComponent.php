<?php

namespace App\Http\Livewire;

use App\Models\ItemSerial;
use App\Models\LocationRack;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SerialFormComponent extends Component
{
    public ?Product $product = null;
    public ?int $productId = null;
    public ?int $locationRackId = null;
    public string $serials = '';

    public array $products = [];
    public array $racks = [];
    public int $createdCount = 0;
    public int $skippedCount = 0;
    public bool $showResult = false;

    protected $rules = [
        'productId' => 'required|exists:products,id',
        'serials' => 'required|string',
    ];

    public function mount(): void
    {
        $this->products = Product::where('has_serial_number', true)
            ->orderBy('name')
            ->get(['id', 'product_code', 'name'])
            ->toArray();

        $this->racks = LocationRack::with('location')
            ->orderBy('code')
            ->get(['id', 'location_id', 'name', 'code'])
            ->toArray();
    }

    public function updatedProductId(): void
    {
        if ($this->productId) {
            $this->product = Product::find($this->productId);
        }
    }

    public function store(): void
    {
        $validated = $this->validate();

        $product = Product::find($validated['productId']);
        if (! $product || ! $product->has_serial_number) {
            session()->flash('error', "Produk tidak dilacak per serial.");
            return;
        }

        $rackId = $validated['locationRackId'] ?? null;
        $rawSerials = preg_split('/[\r\n,;]+/', $validated['serials']);
        $this->createdCount = 0;
        $this->skippedCount = 0;

        DB::transaction(function () use ($product, $rackId, $rawSerials) {
            foreach ($rawSerials as $serial) {
                $serial = trim($serial);
                if ($serial === '') {
                    continue;
                }
                try {
                    ItemSerial::create([
                        'product_id' => $product->id,
                        'location_rack_id' => $rackId,
                        'serial_number' => $serial,
                        'status' => 'available',
                        'created_by' => auth()->id(),
                    ]);
                    $this->createdCount++;
                } catch (\Throwable $e) {
                    $this->skippedCount++;
                }
            }
        });

        $this->showResult = true;
        $this->serials = '';
        $this->productId = null;
        $this->locationRackId = null;
    }

    public function render()
    {
        return view('livewire.item-serials.serial-form-component');
    }
}
<?php

namespace App\Http\Livewire;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\UserLog;
use App\Services\StockLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class SalesFormComponent extends Component
{
    public string $saleChannel = 'toko';
    public ?int $location_id = null;
    public ?int $customer_id = null;

    public string $barcodeInput = '';
    public string $productSearch = '';

    public array $cart = [];

    public string $payment_method = 'cash';
    public float $paid_amount = 0.0;
    public string $payment_reference = '';
    public string $notes = '';

    public bool $hasActiveCashSession = false;
    public ?float $opening_cash = 0.0;
    public bool $showCashSessionModal = false;

    public function mount(string $saleChannel = 'toko'): void
    {
        $this->saleChannel = in_array($saleChannel, ['toko', 'cabang', 'partai'], true) ? $saleChannel : 'toko';

        $user = auth()->user();
        $query = Location::where('is_active', true);
        if ($user && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        $location = $query->first();
        $this->location_id = $location?->id;

        $this->checkCashSession();
    }

    public function checkCashSession(): void
    {
        if (! Schema::hasTable('cash_sessions') || ! $this->location_id) {
            $this->hasActiveCashSession = true;
            return;
        }

        $session = CashSession::query()
            ->where('session_date', now()->toDateString())
            ->where('location_id', $this->location_id)
            ->where('cashier_id', auth()->id())
            ->where('status', 'open')
            ->first();

        $this->hasActiveCashSession = (bool) $session;
    }

    public function openCashSession(): void
    {
        $this->validate([
            'opening_cash' => 'required|numeric|min:0',
            'location_id' => 'required|exists:locations,id',
        ]);

        CashSession::create([
            'session_date' => now()->toDateString(),
            'location_id' => $this->location_id,
            'cashier_id' => auth()->id(),
            'opening_cash' => (float) $this->opening_cash,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $this->hasActiveCashSession = true;
        $this->showCashSessionModal = false;
        session()->flash('success', 'Kas awal berhasil dibuka.');
    }

    public function scanBarcode(): void
    {
        $code = trim($this->barcodeInput);
        if ($code === '') {
            return;
        }

        $product = Product::query()
            ->where('is_active', true)
            ->where(function ($q) use ($code) {
                $q->where('product_code', $code)
                    ->orWhere('barcode', $code)
                    ->orWhereHas('barcodes', fn ($b) => $b->where('barcode', $code));
            })
            ->first();

        if ($product) {
            $this->addProductToCart($product);
            $this->barcodeInput = '';
        } else {
            session()->flash('error', "Produk dengan barcode/kode '{$code}' tidak ditemukan.");
        }
    }

    public function addProductToCart(Product $product): void
    {
        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] === $product->id) {
                $this->cart[$index]['quantity'] += 1;
                $this->cart[$index]['subtotal'] = ($this->cart[$index]['quantity'] * $this->cart[$index]['unit_price']) - $this->cart[$index]['discount_value'];
                return;
            }
        }

        $price = (float) $product->selling_price;
        $this->cart[] = [
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'product_name' => $product->name,
            'unit_name' => $product->sale_unit ?: 'PCS',
            'quantity' => 1.0,
            'unit_price' => $price,
            'discount_value' => 0.0,
            'subtotal' => $price,
        ];
    }

    public function updateQuantity(int $index, float $quantity): void
    {
        if (! isset($this->cart[$index])) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeItem($index);
            return;
        }

        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = ($quantity * $this->cart[$index]['unit_price']) - $this->cart[$index]['discount_value'];
    }

    public function removeItem(int $index): void
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function getSubtotalProperty(): float
    {
        return array_reduce($this->cart, fn ($carry, $item) => $carry + ($item['quantity'] * $item['unit_price']), 0.0);
    }

    public function getDiscountTotalProperty(): float
    {
        return array_reduce($this->cart, fn ($carry, $item) => $carry + (float) ($item['discount_value'] ?? 0), 0.0);
    }

    public function getGrandTotalProperty(): float
    {
        return max(0, $this->subtotal - $this->discountTotal);
    }

    public function getChangeAmountProperty(): float
    {
        return max(0, $this->paid_amount - $this->grandTotal);
    }

    public function submitSale()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang belanja masih kosong.');
            return null;
        }

        if (! $this->hasActiveCashSession) {
            session()->flash('error', 'Silakan buka sesi kas terlebih dahulu.');
            return null;
        }

        $this->validate([
            'location_id' => 'required|exists:locations,id',
            'payment_method' => 'required|in:cash,transfer,qris,tempo',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        if ($this->payment_method === 'cash' && $this->paid_amount < $this->grandTotal) {
            session()->flash('error', 'Jumlah pembayaran tunai kurang dari total belanja.');
            return null;
        }

        $sale = DB::transaction(function () {
            $saleCode = 'TRX-' . now()->format('ymdHis') . '-' . random_int(100, 999);

            $cashSession = CashSession::query()
                ->where('session_date', now()->toDateString())
                ->where('location_id', $this->location_id)
                ->where('cashier_id', auth()->id())
                ->where('status', 'open')
                ->first();

            $sale = Sale::create([
                'sale_code' => $saleCode,
                'sale_channel' => $this->saleChannel,
                'sale_at' => now(),
                'location_id' => $this->location_id,
                'customer_id' => $this->customer_id,
                'cashier_id' => auth()->id(),
                'cash_session_id' => $cashSession?->id,
                'subtotal' => $this->subtotal,
                'discount_total' => $this->discountTotal,
                'grand_total' => $this->grandTotal,
                'paid_amount' => $this->paid_amount ?: $this->grandTotal,
                'change_amount' => $this->changeAmount,
                'payment_method' => $this->payment_method,
                'payment_reference' => $this->payment_reference ?: null,
                'items_count' => count($this->cart),
                'status' => 'paid',
                'notes' => $this->notes ?: null,
            ]);

            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'product_code' => $item['product_code'],
                    'product_name' => $item['product_name'],
                    'unit_name' => $item['unit_name'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_value' => $item['discount_value'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            UserLog::log(
                'CREATE_TRANSACTION',
                "Created sale: {$sale->sale_code} total: {$sale->grand_total}",
                null,
                null,
                $sale->toArray()
            );

            return $sale;
        });

        session()->flash('success', "Transaksi {$sale->sale_code} berhasil disimpan.");
        return redirect()->route('transactions.receipt', $sale->id);
    }

    public function render()
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->limit(50)->get();

        $searchResults = [];
        if (strlen($this->productSearch) >= 2) {
            $searchResults = Product::where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->productSearch}%")
                        ->orWhere('product_code', 'like', "%{$this->productSearch}%");
                })
                ->limit(8)
                ->get();
        }

        return view('livewire.sales.sales-form-component', [
            'locations' => $locations,
            'customers' => $customers,
            'searchResults' => $searchResults,
        ]);
    }
}

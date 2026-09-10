<?php

namespace App\Http\Livewire;

use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceTransaction;
use App\Models\ServiceTransactionItem;
use App\Models\User;
use App\Services\BackOfficeCashService;
use App\Services\StockLedgerService;
use App\Services\AccountingPostingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Livewire\Component;

class ServiceTransactionFormComponent extends Component
{
    // Customer info
    public ?int $customer_id = null;
    public string $customer_code = '';
    public string $customer_name = '';
    public string $customer_phone = '';
    public string $customer_address = '';

    // Device info
    public string $device_brand = '';
    public string $device_type = '';
    public string $serial_number = '';
    public ?string $device_lock_type = null;
    public string $device_lock_value = '';
    public ?int $technician_id = null;
    public float $technician_commission = 0.0;
    public string $check_notes = '';
    public string $complaint = '';
    public string $accessories = '';

    // Service info
    public ?int $location_id = null;
    public string $draftServiceCode = '';
    public string $service_at = '';
    public ?string $return_date = null;
    public ?string $payment_method = 'cash';
    public string $payment_reference = '';
    public float $paid_amount = 0.0;
    public string $status = 'process';
    public bool $is_checked = true;
    public bool $print_detail_price = true;
    public string $invoice_format = 'standart';

    // Items
    public array $items = [];
    public string $serviceSearch = '';
    public string $productSearch = '';

    // Cash session
    public bool $hasActiveCashSession = false;
    public ?float $opening_cash = 0.0;
    public bool $showCashSessionModal = false;

    public function mount(): void
    {
        $user = auth()->user();
        $query = Location::where('is_active', true);
        if ($user && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }
        $location = $query->first();
        $this->location_id = $location?->id;

        $this->draftServiceCode = $this->generateServiceTransactionCode();
        $this->service_at = now()->toDateString();

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

    public function searchServices(): array
    {
        if (strlen($this->serviceSearch) < 2) {
            return [];
        }

        return Service::where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->serviceSearch}%")
                    ->orWhere('service_code', 'like', "%{$this->serviceSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->service_code,
                'unit_price' => (float) $s->price_toko,
                'is_open_price' => $s->is_open_price,
                'allow_discount_override' => $s->allow_discount_override,
            ])
            ->toArray();
    }

    public function searchProducts(): array
    {
        if (strlen($this->productSearch) < 2) {
            return [];
        }

        return Product::where('is_active', true)
            ->where(function ($q) {
                $q->where('name', 'like', "%{$this->productSearch}%")
                    ->orWhere('product_code', 'like', "%{$this->productSearch}%");
            })
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->product_code,
                'unit_price' => (float) $p->selling_price,
                'is_open_price' => $p->is_open_price,
                'allow_discount_override' => false,
            ])
            ->toArray();
    }

    public function addItem(string $type, int $id, string $name, string $code, float $unitPrice, bool $isOpenPrice, bool $allowDiscountOverride): void
    {
        $this->items[] = [
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'code' => $code,
            'quantity' => 1.0,
            'unit_price' => $unitPrice,
            'discount_value' => 0.0,
            'subtotal' => $unitPrice,
            'is_open_price' => $isOpenPrice,
            'allow_discount_override' => $allowDiscountOverride,
        ];
    }

    public function updateItemQuantity(int $index, float $quantity): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        if ($quantity <= 0) {
            $this->removeItem($index);
            return;
        }

        $this->items[$index]['quantity'] = $quantity;
        $this->items[$index]['subtotal'] = max(0, ($quantity * $this->items[$index]['unit_price']) - $this->items[$index]['discount_value']);
    }

    public function updateItemPrice(int $index, float $price): void
    {
        if (! isset($this->items[$index]) || ! $this->items[$index]['is_open_price']) {
            return;
        }

        $this->items[$index]['unit_price'] = $price;
        $this->items[$index]['subtotal'] = max(0, ($this->items[$index]['quantity'] * $price) - $this->items[$index]['discount_value']);
    }

    public function updateItemDiscount(int $index, float $discount): void
    {
        if (! isset($this->items[$index]) || ! $this->items[$index]['allow_discount_override']) {
            return;
        }

        $this->items[$index]['discount_value'] = $discount;
        $this->items[$index]['subtotal'] = max(0, ($this->items[$index]['quantity'] * $this->items[$index]['unit_price']) - $discount);
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getSubtotalProperty(): float
    {
        return array_reduce($this->items, fn ($carry, $item) => $carry + ($item['quantity'] * $item['unit_price']), 0.0);
    }

    public function getDiscountTotalProperty(): float
    {
        return array_reduce($this->items, fn ($carry, $item) => $carry + (float) ($item['discount_value'] ?? 0), 0.0);
    }

    public function getGrandTotalProperty(): float
    {
        return max(0, $this->subtotal - $this->discountTotal);
    }

    public function getChangeAmountProperty(): float
    {
        return $this->payment_method === 'tempo'
            ? max(0, $this->grandTotal - $this->paid_amount)
            : max(0, $this->paid_amount - $this->grandTotal);
    }

    public function submitTransaction()
    {
        if (empty($this->items)) {
            session()->flash('error', 'Minimal pilih satu jasa atau sparepart.');
            return null;
        }

        if (! $this->hasActiveCashSession) {
            session()->flash('error', 'Masukkan kas awal dulu sebelum transaksi service.');
            return null;
        }

        $this->validate([
            'customer_name' => 'required|string|max:255',
            'location_id' => 'required|exists:locations,id',
            'payment_method' => 'required|in:cash,transfer,qris,tempo',
            'paid_amount' => 'required|numeric|min:0',
            'status' => 'required|in:process,done,taken,cancelled',
        ]);

        $isTempo = $this->payment_method === 'tempo';
        $grandTotal = $this->grandTotal;

        if ($isTempo && empty($this->customer_id)) {
            session()->flash('error', 'Pembayaran tempo hanya untuk customer terdaftar.');
            return null;
        }

        if ($isTempo && $this->paid_amount > $grandTotal) {
            session()->flash('error', 'Bayar awal tempo tidak boleh lebih besar dari grand total.');
            return null;
        }

        $cashSession = null;
        if (Schema::hasTable('cash_sessions')) {
            $cashSession = CashSession::query()
                ->where('session_date', now()->toDateString())
                ->where('location_id', $this->location_id)
                ->where('cashier_id', auth()->id())
                ->where('status', 'open')
                ->first();

            if (! $cashSession) {
                session()->flash('error', 'Masukkan kas awal dulu sebelum transaksi service.');
                return null;
            }
        }

        $creditTermDays = $isTempo ? 30 : null;
        $creditDueAt = $isTempo ? Carbon::parse($this->service_at)->addDays($creditTermDays)->toDateString() : null;
        $creditStatus = ! $isTempo
            ? 'paid'
            : ($this->paid_amount >= $grandTotal ? 'paid' : ($this->paid_amount > 0 ? 'partial' : 'unpaid'));

        $transaction = DB::transaction(function () use ($cashSession, $grandTotal, $creditTermDays, $creditDueAt, $creditStatus) {
            $transaction = ServiceTransaction::create([
                'service_code' => $this->draftServiceCode,
                'service_at' => $this->service_at,
                'return_date' => $this->return_date ?: null,
                'customer_id' => $this->customer_id,
                'customer_code' => $this->customer_code ?: null,
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone ?: null,
                'customer_address' => $this->customer_address ?: null,
                'device_brand' => $this->device_brand ?: null,
                'device_type' => $this->device_type ?: null,
                'serial_number' => $this->serial_number ?: null,
                'device_lock_type' => $this->device_lock_type ?: null,
                'device_lock_value' => $this->device_lock_value ?: null,
                'technician_id' => $this->technician_id,
                'technician_commission' => $this->technician_commission,
                'check_notes' => $this->check_notes ?: null,
                'complaint' => $this->complaint ?: null,
                'accessories' => $this->accessories ?: null,
                'subtotal' => $this->subtotal,
                'discount_total' => $this->discountTotal,
                'tax_total' => 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $this->paid_amount,
                'change_amount' => $this->payment_method === 'tempo' ? 0 : ($this->paid_amount - $grandTotal),
                'location_id' => $this->location_id,
                'cashier_id' => auth()->id(),
                'cash_session_id' => $cashSession?->id,
                'payment_method' => $this->payment_method,
                'payment_reference' => $this->payment_reference ?: null,
                'credit_term_days' => $creditTermDays,
                'credit_due_at' => $creditDueAt,
                'credit_status' => $creditStatus,
                'status' => $this->status,
                'is_checked' => $this->is_checked,
                'print_detail_price' => $this->print_detail_price,
                'invoice_format' => $this->invoice_format ?: 'standart',
            ]);

            foreach ($this->items as $item) {
                ServiceTransactionItem::create([
                    'service_transaction_id' => $transaction->id,
                    'item_type' => $item['type'],
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'code' => $item['code'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'purchase_price' => $item['type'] === 'product' ? (float) (Product::find($item['id'])?->purchase_price ?? 0) : 0.0,
                    'discount_value' => $item['discount_value'] ?? 0.0,
                    'subtotal' => $item['subtotal'],
                    'is_open_price' => $item['is_open_price'],
                    'allow_discount_override' => $item['allow_discount_override'],
                ]);
            }

            return $transaction->fresh(['items', 'cashier', 'technician', 'location', 'customer']);
        });

        session()->flash('success', "Transaksi service {$transaction->service_code} berhasil disimpan.");
        return redirect()->route('service-transactions.show', $transaction->id);
    }

    private function generateServiceTransactionCode(): string
    {
        $prefix = 'SRV';

        do {
            $code = $prefix . '-' . now()->format('ymdHis') . '-' . random_int(100, 999);
        } while (Schema::hasTable('service_transactions') && ServiceTransaction::where('service_code', $code)->exists());

        return $code;
    }

    public function render()
    {
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $technicians = User::orderBy('name')->get(['id', 'name']);

        return view('livewire.service-transactions.service-transaction-form-component', [
            'locations' => $locations,
            'technicians' => $technicians,
        ]);
    }
}

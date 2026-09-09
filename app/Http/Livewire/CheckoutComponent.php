<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\Location;
use App\Services\CartService;
use App\Services\ShippingService;
use Livewire\Component;

class CheckoutComponent extends Component
{
    public int $currentStep = 1;

    public string $deliveryMethod = 'instant';

    public string $paymentMethod = 'va';

    public ?string $address = null;

    public ?string $city = null;

    public ?string $postalCode = null;

    public ?string $notes = null;

    public ?int $pickupLocationId = null;

    public ?string $pickupTime = null;

    public ?float $lat = null;

    public ?float $lng = null;

    public ?string $shippingProvider = null;

    public array $cartSummary = [
        'subtotal' => 0,
        'discount' => 0,
        'total' => 0,
        'item_count' => 0,
    ];

    public array $deliveryOptions = [];

    public array $paymentMethods = [];

    public array $pickupLocations = [];

    public array $customer = [];

    public string $toastMessage = '';

    public bool $showToast = false;

    public function mount(): void
    {
        $this->deliveryOptions = [
            ['code' => 'instant', 'name' => 'Instant (GoSend / Grab)', 'desc' => 'Diantar kurir dalam hitungan menit', 'eta' => '± 30 menit', 'cost' => 15000, 'icon' => 'zap'],
            ['code' => 'expedition', 'name' => 'Ekspedisi (J&T / JNE)', 'desc' => 'Dikirim via kurir ekspedisi', 'eta' => '2–3 hari', 'cost' => 12000, 'icon' => 'truck'],
            ['code' => 'pickup', 'name' => 'Ambil di Toko', 'desc' => 'Ambil langsung di lokasi toko', 'eta' => 'Gratis', 'cost' => 0, 'icon' => 'store'],
        ];

        $this->paymentMethods = [
            ['code' => 'va', 'name' => 'Transfer Bank (Virtual Account)', 'desc' => 'BCA, BRI, Mandiri, Permata', 'icon' => 'landmark'],
            ['code' => 'ewallet', 'name' => 'E-Wallet', 'desc' => 'GoPay, OVO, DANA, ShopeePay', 'icon' => 'wallet'],
            ['code' => 'qris', 'name' => 'QRIS', 'desc' => 'Scan QR dari aplikasi apa pun', 'icon' => 'qr'],
            ['code' => 'card', 'name' => 'Kartu Kredit', 'desc' => 'Visa, Mastercard, JCB', 'icon' => 'credit-card'],
        ];

        $this->pickupLocations = Location::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        $this->customer = $this->customerData();
        $this->address = $this->customer['address'] ?? null;

        $this->refreshCart();
    }

    public function selectDelivery(string $code): void
    {
        $this->deliveryMethod = $code;
    }

    public function selectPayment(string $code): void
    {
        $this->paymentMethod = $code;
    }

    public function submitOrder(): void
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            $this->toast('Keranjang belanja masih kosong.');

            return;
        }

        $rules = [
            'deliveryMethod' => 'required|in:instant,expedition,pickup',
            'paymentMethod' => 'required|string|max:50',
            'address' => 'required_if:deliveryMethod,instant,expedition|string|max:500',
            'city' => 'nullable|string|max:100',
            'postalCode' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'pickupLocationId' => 'nullable|integer|exists:locations,id',
            'pickupTime' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'shippingProvider' => 'nullable|string|max:50',
        ];

        $this->validate($rules);

        $shippingCost = match ($this->deliveryMethod) {
            'instant' => 15000,
            'pickup' => 0,
            default => app(ShippingService::class)->calculateRate(
                $this->shippingProvider ?? 'jnt',
                config('app.shipping_origin_city', 'Jakarta'),
                $this->city ?? '',
                (float) $cart->items->sum('quantity') * 0.5
            ),
        };

        $orderData = [
            'order_type' => $this->deliveryMethod === 'expedition' ? 'delivery' : $this->deliveryMethod,
            'guest_name' => null,
            'guest_email' => null,
            'guest_phone' => null,
            'shipping_address' => $this->address,
            'shipping_city' => $this->city,
            'shipping_postal_code' => $this->postalCode,
            'shipping_cost' => $shippingCost,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
            'pickup_location_id' => $this->pickupLocationId,
            'pickup_time' => $this->pickupTime,
            'delivery_lat' => $this->lat,
            'delivery_lng' => $this->lng,
        ];

        try {
            $order = $this->cartService()->convertToOrder($cart, $orderData);
        } catch (\RuntimeException $e) {
            $this->toast($e->getMessage());

            return;
        }

        $this->redirectRoute('website.order.show', $order->order_code);
    }

    public function render()
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());
        $cart->load('items.product.images');
        $totals = $this->cartService()->calculateTotals($cart);

        return view('livewire.checkout-component', [
            'cartItems' => $cart->items,
            'cartSummary' => [
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount_total'],
                'total' => $totals['grand_total'],
                'item_count' => $totals['item_count'],
            ],
        ]);
    }

    private function refreshCart(): void
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());
        $cart->load('items.product.images');
        $totals = $this->cartService()->calculateTotals($cart);

        $this->cartSummary = [
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount_total'],
            'total' => $totals['grand_total'],
            'item_count' => $totals['item_count'],
        ];
    }

    private function cartService(): CartService
    {
        return app(CartService::class);
    }

    private function toast(string $message): void
    {
        $this->toastMessage = $message;
        $this->showToast = true;
    }

    private function customerData(): array
    {
        $session = session('website_customer');
        $id = is_array($session) ? (int) ($session['id'] ?? 0) : 0;

        if ($id <= 0) {
            return [];
        }

        $customer = Customer::find($id);

        if (! $customer) {
            return [];
        }

        return [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address' => $customer->address ?? '',
            'points_balance' => $customer->points_balance,
        ];
    }

    private function customerId(): ?int
    {
        $customer = session('website_customer');
        $id = is_array($customer) ? (int) ($customer['id'] ?? 0) : 0;

        return $id > 0 ? $id : null;
    }

    private function sessionId(): string
    {
        return session()->getId();
    }
}

<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Location;
use App\Services\CartService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index()
    {
        $cart = $this->cartService->getCart($this->customerId(), $this->sessionId());
        $cart->load('items.product.images');

        if ($cart->items->isEmpty()) {
            return redirect()->route('website.cart')->with('info', 'Keranjang belanja masih kosong.');
        }

        $totals = $this->cartService->calculateTotals($cart);

        $deliveryOptions = collect([
            (object) ['code' => 'instant', 'name' => 'Instant (GoSend / Grab)', 'desc' => 'Diantar kurir dalam hitungan menit', 'eta' => '± 30 menit', 'cost' => 15000, 'icon' => 'zap'],
            (object) ['code' => 'expedition', 'name' => 'Ekspedisi (J&T / JNE)', 'desc' => 'Dikirim via kurir ekspedisi', 'eta' => '2–3 hari', 'cost' => 12000, 'icon' => 'truck'],
            (object) ['code' => 'pickup', 'name' => 'Ambil di Toko', 'desc' => 'Ambil langsung di lokasi toko', 'eta' => 'Gratis', 'cost' => 0, 'icon' => 'store'],
        ]);

        $paymentMethods = collect([
            (object) ['code' => 'va', 'name' => 'Transfer Bank (Virtual Account)', 'desc' => 'BCA, BRI, Mandiri, Permata', 'icon' => 'landmark'],
            (object) ['code' => 'ewallet', 'name' => 'E-Wallet', 'desc' => 'GoPay, OVO, DANA, ShopeePay', 'icon' => 'wallet'],
            (object) ['code' => 'qris', 'name' => 'QRIS', 'desc' => 'Scan QR dari aplikasi apa pun', 'icon' => 'qr'],
            (object) ['code' => 'card', 'name' => 'Kartu Kredit', 'desc' => 'Visa, Mastercard, JCB', 'icon' => 'credit-card'],
        ]);

        return view('website.checkout.index', [
            'cartItems' => $cart->items,
            'cartSummary' => [
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount_total'],
                'total' => $totals['grand_total'],
                'item_count' => $totals['item_count'],
            ],
            'deliveryOptions' => $deliveryOptions,
            'paymentMethods' => $paymentMethods,
            'pickupLocations' => Location::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'customer' => $this->customerData(),
        ]);
    }

    public function store(Request $request)
    {
        $cart = $this->cartService->getCart($this->customerId(), $this->sessionId());
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            return back()->withErrors(['cart' => 'Keranjang belanja masih kosong.']);
        }

        $validated = $request->validate([
            'delivery_method' => 'required|in:instant,expedition,pickup',
            'payment_method' => 'required|string|max:50',
            'address' => 'required_if:delivery_method,instant,expedition|string|max:500',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'pickup_location_id' => 'nullable|integer|exists:locations,id',
            'pickup_time' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $shippingCost = match ($validated['delivery_method']) {
            'instant' => 15000,
            'expedition' => 12000,
            default => 0,
        };

        $orderData = [
            'order_type' => $validated['delivery_method'] === 'expedition' ? 'delivery' : $validated['delivery_method'],
            'guest_name' => null,
            'guest_email' => null,
            'guest_phone' => null,
            'shipping_address' => $validated['address'] ?? null,
            'shipping_city' => $validated['city'] ?? null,
            'shipping_postal_code' => $validated['postal_code'] ?? null,
            'shipping_cost' => $shippingCost,
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'pickup_location_id' => $validated['pickup_location_id'] ?? null,
            'pickup_time' => $validated['pickup_time'] ?? null,
            'delivery_lat' => $validated['lat'] ?? null,
            'delivery_lng' => $validated['lng'] ?? null,
        ];

        try {
            $order = $this->cartService->convertToOrder($cart, $orderData);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }

        return redirect()->route('website.order.show', $order->order_code);
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
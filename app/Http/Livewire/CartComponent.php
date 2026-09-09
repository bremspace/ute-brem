<?php

namespace App\Http\Livewire;

use App\Models\CartItem;
use App\Services\CartService;
use Livewire\Component;

class CartComponent extends Component
{
    public array $cartSummary = [
        'subtotal' => 0,
        'discount' => 0,
        'total' => 0,
        'item_count' => 0,
    ];

    public ?array $customer = null;

    public string $toastMessage = '';

    public bool $showToast = false;

    public function mount(): void
    {
        $this->refreshCart();
    }

    public function addItem(int $productId, float $quantity = 1): void
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());

        try {
            $this->cartService()->addItem($cart, $productId, $quantity);
            $this->toast('Produk ditambahkan ke keranjang.');
        } catch (\RuntimeException $e) {
            $this->toast($e->getMessage());
        }

        $this->refreshCart();
    }

    public function updateQuantity(int $cartItemId, float $quantity): void
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());

        $item = CartItem::find($cartItemId);

        if (! $item || (int) $item->cart_id !== (int) $cart->id) {
            $this->toast('Item tidak ditemukan.');
            $this->refreshCart();

            return;
        }

        try {
            $this->cartService()->updateItemQuantity($cart, $cartItemId, $quantity);
            $this->toast('Jumlah item diperbarui.');
        } catch (\RuntimeException $e) {
            $this->toast($e->getMessage());
        }

        $this->refreshCart();
    }

    public function removeItem(int $cartItemId): void
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());

        $item = CartItem::find($cartItemId);

        if (! $item || (int) $item->cart_id !== (int) $cart->id) {
            $this->toast('Item tidak ditemukan.');
            $this->refreshCart();

            return;
        }

        $this->cartService()->removeItem($cart, $cartItemId);
        $this->toast('Item dihapus dari keranjang.');
        $this->refreshCart();
    }

    public function clear(): void
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());
        $this->cartService()->clearCart($cart);
        $this->toast('Keranjang dikosongkan.');
        $this->refreshCart();
    }

    public function render()
    {
        $cart = $this->cartService()->getCart($this->customerId(), $this->sessionId());
        $cart->load('items.product.images');
        $totals = $this->cartService()->calculateTotals($cart);

        return view('livewire.cart-component', [
            'cartItems' => $cart->items,
            'cartSummary' => [
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount_total'],
                'total' => $totals['grand_total'],
                'item_count' => $totals['item_count'],
            ],
            'customer' => $this->customer,
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

        $this->customer = $this->customerData();
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

    private function customerData(): ?array
    {
        $session = session('website_customer');
        $id = is_array($session) ? (int) ($session['id'] ?? 0) : 0;

        return $id > 0 ? ['id' => $id] : null;
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

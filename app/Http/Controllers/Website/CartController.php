<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index()
    {
        $cart = $this->cartService->getCart($this->customerId(), $this->sessionId());
        $cart->load('items.product.images');
        $totals = $this->cartService->calculateTotals($cart);

        return view('website.cart.index', [
            'cartItems' => $cart->items,
            'cartSummary' => [
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount_total'],
                'total' => $totals['grand_total'],
                'item_count' => $totals['item_count'],
            ],
            'customer' => session('website_customer'),
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $cart = $this->cartService->getCart($this->customerId(), $this->sessionId());

        try {
            $item = $this->cartService->addItem($cart, (int) $validated['product_id'], (float) $validated['quantity']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $totals = $this->cartService->calculateTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Produk ditambahkan ke keranjang.',
            'item' => $item,
            'cart_count' => $totals['item_count'],
            'totals' => $totals,
        ]);
    }

    public function update(Request $request, CartItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $cart = $this->cartService->getCart($this->customerId(), $this->sessionId());

        if ((int) $item->cart_id !== (int) $cart->id) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
        }

        try {
            $item = $this->cartService->updateItemQuantity($cart, (int) $item->id, (float) $validated['quantity']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $totals = $this->cartService->calculateTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Jumlah item diperbarui.',
            'item' => $item,
            'cart_count' => $totals['item_count'],
            'totals' => $totals,
        ]);
    }

    public function remove(CartItem $item)
    {
        $cart = $this->cartService->getCart($this->customerId(), $this->sessionId());

        if ((int) $item->cart_id !== (int) $cart->id) {
            return response()->json(['success' => false, 'message' => 'Item tidak ditemukan.'], 404);
        }

        $this->cartService->removeItem($cart, (int) $item->id);
        $totals = $this->cartService->calculateTotals($cart);

        return response()->json([
            'success' => true,
            'message' => 'Item dihapus dari keranjang.',
            'cart_count' => $totals['item_count'],
            'totals' => $totals,
        ]);
    }

    public function clear()
    {
        $cart = $this->cartService->getCart($this->customerId(), $this->sessionId());
        $this->cartService->clearCart($cart);

        return response()->json([
            'success' => true,
            'message' => 'Keranjang dikosongkan.',
            'cart_count' => 0,
        ]);
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
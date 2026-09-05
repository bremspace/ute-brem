<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\OnlineOrder;
use App\Models\OnlineOrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Get the active cart for a customer/session, creating one when missing.
     */
    public function getCart(?int $customerId, ?string $sessionId): Cart
    {
        $sessionId = $sessionId ?: (string) Str::uuid();

        $cart = $customerId
            ? Cart::where('customer_id', $customerId)
                ->where('status', 'active')
                ->latest('updated_at')
                ->first()
            : Cart::where('session_id', $sessionId)
                ->whereNull('customer_id')
                ->where('status', 'active')
                ->latest('updated_at')
                ->first();

        if (! $cart) {
            $cart = Cart::create([
                'customer_id' => $customerId,
                'session_id' => $sessionId,
                'status' => 'active',
            ]);
        }

        return $cart;
    }

    /**
     * Add a product to the cart. Merges quantity when the product is already present.
     *
     * @throws \RuntimeException when the product is unavailable or stock is insufficient
     */
    public function addItem(Cart $cart, int $productId, float $quantity = 1): CartItem
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Jumlah item harus lebih dari 0.');
        }

        $product = Product::where('id', $productId)
            ->where('is_active', true)
            ->where('is_published', true)
            ->first();

        if (! $product) {
            throw new \RuntimeException('Produk tidak ditemukan atau tidak tersedia.');
        }

        if ($product->is_member_only && ! $cart->customer_id) {
            throw new \RuntimeException('Produk ini khusus untuk member. Silakan login terlebih dahulu.');
        }

        $availableStock = $this->availableStock($product);

        $existing = $cart->items()->where('product_id', $productId)->first();

        if ($existing) {
            $newQuantity = (float) $existing->quantity + $quantity;
            if ($availableStock < $newQuantity) {
                throw new \RuntimeException(
                    "Stok produk '{$product->name}' tidak mencukupi. Tersedia: {$availableStock}."
                );
            }

            $existing->update(['quantity' => $newQuantity]);

            return $existing->refresh();
        }

        if ($availableStock < $quantity) {
            throw new \RuntimeException(
                "Stok produk '{$product->name}' tidak mencukupi. Tersedia: {$availableStock}."
            );
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => (float) $product->selling_price,
            'discount_amount' => 0,
            'notes' => null,
        ]);
    }

    /**
     * Update the quantity of an existing cart item.
     *
     * @throws \RuntimeException when the item is not in the cart or stock is insufficient
     */
    public function updateItemQuantity(Cart $cart, int $cartItemId, float $quantity): CartItem
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Jumlah item harus lebih dari 0.');
        }

        $item = $cart->items()->find($cartItemId);

        if (! $item) {
            throw new \RuntimeException('Item tidak ditemukan di keranjang.');
        }

        $availableStock = $this->availableStock($item->product);

        if ($availableStock < $quantity) {
            throw new \RuntimeException(
                "Stok produk '{$item->product->name}' tidak mencukupi. Tersedia: {$availableStock}."
            );
        }

        $item->update(['quantity' => $quantity]);

        return $item->refresh();
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(Cart $cart, int $cartItemId): bool
    {
        $item = $cart->items()->find($cartItemId);

        if (! $item) {
            return false;
        }

        return (bool) $item->delete();
    }

    /**
     * Remove all items from the cart.
     */
    public function clearCart(Cart $cart): bool
    {
        $cart->items()->delete();

        return true;
    }

    /**
     * Calculate cart totals (subtotal, discount, grand total, counts).
     */
    public function calculateTotals(Cart $cart): array
    {
        $cart->loadMissing('items.product');

        $subtotal = 0.0;
        $discountTotal = 0.0;
        $itemCount = 0;
        $quantityCount = 0.0;

        foreach ($cart->items as $item) {
            $subtotal += (float) $item->quantity * (float) $item->unit_price;
            $discountTotal += (float) $item->discount_amount;
            $itemCount++;
            $quantityCount += (float) $item->quantity;
        }

        $grandTotal = $subtotal - $discountTotal;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_total' => round($discountTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'item_count' => $itemCount,
            'quantity_count' => round($quantityCount, 2),
        ];
    }

    /**
     * Convert the cart into an online order and mark the cart as converted.
     *
     * @throws \RuntimeException when the cart is empty
     */
    public function convertToOrder(Cart $cart, array $orderData): OnlineOrder
    {
        $cart->load('items.product');

        if ($cart->items->isEmpty()) {
            throw new \RuntimeException('Keranjang belanja masih kosong.');
        }

        $totals = $this->calculateTotals($cart);
        $shippingCost = (float) ($orderData['shipping_cost'] ?? 0);

        return DB::transaction(function () use ($cart, $orderData, $totals, $shippingCost) {
            $order = OnlineOrder::create([
                'order_code' => $this->generateOrderCode(),
                'customer_id' => $cart->customer_id,
                'guest_name' => $orderData['guest_name'] ?? null,
                'guest_email' => $orderData['guest_email'] ?? null,
                'guest_phone' => $orderData['guest_phone'] ?? null,
                'order_type' => $orderData['order_type'],
                'status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'shipping_cost' => $shippingCost,
                'grand_total' => $totals['grand_total'] + $shippingCost,
                'payment_method' => $orderData['payment_method'] ?? null,
                'shipping_address' => $orderData['shipping_address'] ?? null,
                'shipping_city' => $orderData['shipping_city'] ?? null,
                'shipping_province' => $orderData['shipping_province'] ?? null,
                'shipping_postal_code' => $orderData['shipping_postal_code'] ?? null,
                'delivery_lat' => $orderData['delivery_lat'] ?? null,
                'delivery_lng' => $orderData['delivery_lng'] ?? null,
                'pickup_location_id' => $orderData['pickup_location_id'] ?? null,
                'pickup_time' => $orderData['pickup_time'] ?? null,
                'notes' => $orderData['notes'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                OnlineOrderItem::create([
                    'online_order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_code' => $item->product->product_code,
                    'quantity' => $item->quantity,
                    'unit' => $item->product->sale_unit ?: 'pcs',
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'subtotal' => ((float) $item->quantity * (float) $item->unit_price) - (float) $item->discount_amount,
                    'notes' => $item->notes,
                ]);
            }

            $cart->update(['status' => 'converted']);

            return $order;
        });
    }

    /**
     * Generate a unique order code in the format ORD-YYYYMMDD-XXXX.
     */
    public function generateOrderCode(): string
    {
        $today = now()->format('Ymd');
        $count = OnlineOrder::whereDate('created_at', now()->toDateString())->count();
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return "ORD-{$today}-{$sequence}";
    }

    private function availableStock(Product $product): float
    {
        return (float) $product->stock_global;
    }
}
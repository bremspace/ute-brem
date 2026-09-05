<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteCartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fixed 40-char session ID (Laravel requires /^[a-zA-Z0-9]{40}$/).
     * The test client does not persist session cookies between requests,
     * so we pin the cookie to keep the guest cart stable across requests.
     */
    private const SESSION_ID = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0';

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create([
            'name' => 'LCD Screen',
            'slug' => 'lcd-screen',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'LCD Screen iPhone 12',
            'product_code' => 'LCD-IPH12',
            'slug' => 'lcd-screen-iphone-12',
            'sku' => 'LCD-IPH12-SKU',
            'purchase_price' => 500000,
            'selling_price' => 750000,
            'sale_unit' => 'pcs',
            'stock_global' => 10,
            'is_published' => true,
            'is_member_only' => false,
            'is_active' => true,
        ]);
    }

    private function withPinnedSession(): static
    {
        return $this->withCookie(config('session.cookie'), self::SESSION_ID);
    }

    public function test_guest_can_view_cart_page(): void
    {
        $this->get(route('website.cart'))
            ->assertOk()
            ->assertSee('Keranjang');
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $response = $this->withPinnedSession()->post(route('website.cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'unit_price' => 750000,
        ]);
    }

    public function test_guest_cannot_add_member_only_product(): void
    {
        $this->product->update(['is_member_only' => true]);

        $response = $this->withPinnedSession()->post(route('website.cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_cannot_add_more_than_available_stock(): void
    {
        $response = $this->withPinnedSession()->post(route('website.cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 99,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_can_update_cart_item_quantity(): void
    {
        $this->withPinnedSession()->post(route('website.cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $item = CartItem::first();

        $response = $this->put(route('website.cart.update', $item), [
            'quantity' => 5,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 5,
        ]);
    }

    public function test_guest_can_remove_cart_item(): void
    {
        $this->withPinnedSession()->post(route('website.cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $item = CartItem::first();

        $response = $this->delete(route('website.cart.remove', $item));

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_guest_can_clear_cart(): void
    {
        $this->withPinnedSession()->post(route('website.cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response = $this->post(route('website.cart.clear'));

        $response->assertOk()->assertJson(['success' => true, 'cart_count' => 0]);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_can_checkout_and_create_order(): void
    {
        $this->withPinnedSession()->post(route('website.cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->post(route('website.checkout.store'), [
            'delivery_method' => 'instant',
            'payment_method' => 'va',
            'address' => 'Jl. Test No. 1',
            'city' => 'Jakarta',
            'postal_code' => '12345',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('online_orders', [
            'order_type' => 'instant',
            'payment_method' => 'va',
            'subtotal' => 1500000,
            'shipping_cost' => 15000,
            'grand_total' => 1515000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('online_order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'subtotal' => 1500000,
        ]);

        // Cart should be marked as converted
        $this->assertDatabaseHas('carts', [
            'status' => 'converted',
        ]);
    }

    public function test_order_code_uses_expected_format(): void
    {
        $service = app(CartService::class);
        $code = $service->generateOrderCode();

        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{4}$/', $code);
    }
}
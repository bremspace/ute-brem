<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('online_orders')) {
            Schema::create('online_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_code', 32)->unique();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('guest_name', 100)->nullable();
                $table->string('guest_email', 100)->nullable();
                $table->string('guest_phone', 20)->nullable();

                // Order details
                $table->enum('order_type', ['delivery', 'pickup', 'instant']);
                $table->enum('status', [
                    'pending', 'paid', 'processing', 'ready_for_pickup',
                    'shipped', 'in_transit', 'delivered', 'completed',
                    'cancelled', 'refunded',
                ])->default('pending');

                // Financials
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount_total', 12, 2)->default(0);
                $table->decimal('shipping_cost', 12, 2)->default(0);
                $table->decimal('grand_total', 12, 2)->default(0);

                // Payment
                $table->string('payment_method', 50)->nullable();
                $table->string('payment_gateway', 50)->nullable();
                $table->string('payment_reference', 100)->nullable();
                $table->timestamp('paid_at')->nullable();

                // Shipping
                $table->string('shipping_provider', 50)->nullable();
                $table->string('shipping_service', 50)->nullable();
                $table->string('shipping_tracking_number', 100)->nullable();
                $table->text('shipping_address')->nullable();
                $table->string('shipping_city', 100)->nullable();
                $table->string('shipping_province', 100)->nullable();
                $table->string('shipping_postal_code', 10)->nullable();

                // Delivery
                $table->decimal('delivery_lat', 10, 8)->nullable();
                $table->decimal('delivery_lng', 11, 8)->nullable();
                $table->text('delivery_notes')->nullable();

                // Pickup
                $table->foreignId('pickup_location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->timestamp('pickup_time')->nullable();

                // Points
                $table->unsignedInteger('points_earned')->default(0);
                $table->unsignedInteger('points_used')->default(0);

                // Metadata
                $table->text('notes')->nullable();
                $table->text('internal_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                $table->index('status');
                $table->index('order_type');
                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('online_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('online_order_items')) {
            Schema::create('online_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('online_order_id')->constrained('online_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->string('product_name');
                $table->string('product_code', 50);
                $table->decimal('quantity', 10, 2)->default(1);
                $table->string('unit', 20)->default('pcs');
                $table->decimal('unit_price', 12, 2);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('subtotal', 12, 2);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('online_order_items');
    }
};

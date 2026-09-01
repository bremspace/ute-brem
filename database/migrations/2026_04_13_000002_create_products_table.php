<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->nullable();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('legacy_image_path')->nullable();
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2);
            $table->decimal('selling_price_margin_percent', 8, 2)->nullable();
            $table->date('expired_date')->nullable();
            $table->boolean('has_serial_number')->default(false);
            $table->string('buy_unit', 50)->nullable();
            $table->string('sale_unit', 50)->nullable();
            $table->decimal('default_conversion_qty', 15, 2)->default(1);
            $table->decimal('stock_global', 15, 2)->default(0);
            $table->decimal('stock_min', 15, 2)->nullable();
            $table->decimal('stock_max', 15, 2)->nullable();
            $table->decimal('damaged_stock', 15, 2)->default(0);
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->decimal('member_point', 15, 2)->nullable();
            $table->decimal('staff_point', 15, 2)->nullable();
            $table->decimal('sales_commission', 15, 2)->nullable();
            $table->date('last_purchase_date')->nullable();
            $table->string('rack_location')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('unit', 50)->default('pcs');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_open_price')->default(false);
            $table->boolean('allow_discount_override')->default(false);
            $table->boolean('sync_sell_price_to_branch')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['category_id', 'is_active']);
            $table->index(['name', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

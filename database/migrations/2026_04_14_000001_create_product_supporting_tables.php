<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_makers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('location_racks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['location_id', 'name']);
        });

        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 50)->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->enum('type', ['regular', 'member'])->default('regular');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_product_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_type_id')->constrained('product_types')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'product_type_id']);
        });

        Schema::create('product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('barcode');
            $table->unsignedTinyInteger('unit_level')->default(1);
            $table->string('label')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('unit_name');
            $table->decimal('conversion_qty', 15, 2)->default(1);
            $table->decimal('price_toko', 15, 2)->nullable();
            $table->decimal('margin_toko', 8, 2)->nullable();
            $table->decimal('price_partai', 15, 2)->nullable();
            $table->decimal('margin_partai', 8, 2)->nullable();
            $table->decimal('price_cabang', 15, 2)->nullable();
            $table->decimal('margin_cabang', 8, 2)->nullable();
            $table->decimal('price_lain', 15, 2)->nullable();
            $table->decimal('margin_lain', 8, 2)->nullable();
            $table->string('barcode')->nullable();
            $table->string('barcode_label')->nullable();
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedTinyInteger('unit_level')->default(1);
            $table->unsignedInteger('min_qty')->default(1);
            $table->decimal('price', 15, 2);
            $table->timestamps();
        });

        Schema::create('product_customer_group_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained('customer_groups')->cascadeOnDelete();
            $table->enum('channel', ['toko', 'partai'])->default('toko');
            $table->decimal('price', 15, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('stock', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_customer_group_prices');
        Schema::dropIfExists('product_price_tiers');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('product_barcodes');
        Schema::dropIfExists('product_product_type');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
        Schema::dropIfExists('location_racks');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('product_makers');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('sub_categories');
    }
};

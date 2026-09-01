<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('services')) {
            Schema::create('services', function (Blueprint $table) {
                $table->id();
                $table->string('service_code', 80)->unique();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('category')->nullable();
                $table->string('group')->nullable();
                $table->decimal('price_toko', 15, 2)->default(0);
                $table->decimal('price_partai', 15, 2)->default(0);
                $table->decimal('price_cabang', 15, 2)->default(0);
                $table->string('image_path')->nullable();
                $table->string('image_note')->nullable();
                $table->boolean('is_taxable')->default(false);
                $table->boolean('is_open_price')->default(false);
                $table->boolean('allow_discount_override')->default(false);
                $table->boolean('is_published')->default(false);
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('service_transactions')) {
            Schema::create('service_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('service_code', 80)->unique();
                $table->dateTime('service_at')->index();
                $table->date('return_date')->nullable();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->string('customer_code')->nullable();
                $table->string('customer_name');
                $table->string('customer_phone')->nullable();
                $table->text('customer_address')->nullable();
                $table->string('device_brand')->nullable();
                $table->string('device_type')->nullable();
                $table->string('serial_number')->nullable();
                $table->string('device_lock_type', 20)->nullable();
                $table->string('device_lock_value')->nullable();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('technician_commission', 15, 2)->default(0);
                $table->text('check_notes')->nullable();
                $table->text('complaint')->nullable();
                $table->text('accessories')->nullable();
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('tax_total', 15, 2)->default(0);
                $table->decimal('grand_total', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->decimal('change_amount', 15, 2)->default(0);
                $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('cash_session_id')->nullable()->constrained('cash_sessions')->nullOnDelete();
                $table->string('payment_method', 30)->default('cash');
                $table->string('payment_reference', 120)->nullable();
                $table->enum('status', ['process', 'done', 'taken', 'cancelled'])->default('process');
                $table->boolean('is_checked')->default(false);
                $table->boolean('print_detail_price')->default(true);
                $table->string('invoice_format', 50)->default('standart');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_transaction_items')) {
            Schema::create('service_transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_transaction_id')->constrained('service_transactions')->cascadeOnDelete();
                $table->string('item_type', 20); // service|product
                $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->string('code', 120)->nullable();
                $table->string('name');
                $table->decimal('quantity', 15, 2)->default(1);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->boolean('is_open_price')->default(false);
                $table->boolean('allow_discount_override')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_transaction_items');
        Schema::dropIfExists('service_transactions');
        Schema::dropIfExists('services');
    }
};

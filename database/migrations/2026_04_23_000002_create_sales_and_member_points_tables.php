<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->string('sale_code', 80)->unique();
                $table->dateTime('sale_at')->index();
                $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();

                $table->decimal('subtotal', 15, 2)->default(0);
                $table->decimal('discount_total', 15, 2)->default(0);
                $table->decimal('grand_total', 15, 2)->default(0);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->decimal('change_amount', 15, 2)->default(0);

                $table->unsignedInteger('items_count')->default(0);
                $table->unsignedInteger('points_earned')->default(0);

                $table->enum('status', ['paid', 'void'])->default('paid');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

                $table->string('product_code', 120)->nullable();
                $table->string('product_name');
                $table->string('barcode', 120)->nullable();

                $table->unsignedTinyInteger('unit_level')->default(1);
                $table->string('unit_name', 50)->nullable();
                $table->decimal('conversion_qty', 15, 2)->default(1);

                $table->decimal('quantity', 15, 2)->default(1);
                $table->decimal('base_quantity', 15, 2)->default(1);
                $table->decimal('unit_price', 15, 2)->default(0);
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);

                // For products that require serial numbers (voucher/pulsa/etc).
                $table->json('serial_numbers')->nullable();

                $table->timestamps();
            });
        }

        if (! Schema::hasTable('customer_point_ledgers')) {
            Schema::create('customer_point_ledgers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
                $table->integer('points'); // can be negative for redemption later
                $table->integer('balance_after');
                $table->string('source', 50)->default('sale');
                $table->string('reference_code', 80)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('customers') && ! Schema::hasColumn('customers', 'points_balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->integer('points_balance')->default(0)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_point_ledgers');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');

        if (Schema::hasTable('customers') && Schema::hasColumn('customers', 'points_balance')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('points_balance');
            });
        }
    }
};


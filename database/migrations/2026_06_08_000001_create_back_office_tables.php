<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('back_office_cash_accounts')) {
            Schema::create('back_office_cash_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->string('type', 30)->default('cash');
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->decimal('current_balance', 15, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('back_office_cost_categories')) {
            Schema::create('back_office_cost_categories', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->string('type', 20)->default('expense');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('back_office_cash_transactions')) {
            Schema::create('back_office_cash_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('transaction_code', 80)->unique();
                $table->date('transaction_date')->index();
                $table->string('transaction_type', 30)->index();
                $table->foreignId('cash_account_id')->nullable()->constrained('back_office_cash_accounts')->nullOnDelete();
                $table->foreignId('target_cash_account_id')->nullable()->constrained('back_office_cash_accounts')->nullOnDelete();
                $table->foreignId('cost_category_id')->nullable()->constrained('back_office_cost_categories')->nullOnDelete();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('description')->nullable();
                $table->string('reference', 120)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('back_office_employee_advances')) {
            Schema::create('back_office_employee_advances', function (Blueprint $table) {
                $table->id();
                $table->string('advance_code', 80)->unique();
                $table->date('advance_date')->index();
                $table->foreignId('employee_id')->constrained('users')->restrictOnDelete();
                $table->foreignId('cash_account_id')->constrained('back_office_cash_accounts')->restrictOnDelete();
                $table->decimal('amount', 15, 2);
                $table->decimal('paid_amount', 15, 2)->default(0);
                $table->string('status', 20)->default('open');
                $table->string('description')->nullable();
                $table->foreignId('cash_transaction_id')->nullable()->constrained('back_office_cash_transactions')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('back_office_stock_documents')) {
            Schema::create('back_office_stock_documents', function (Blueprint $table) {
                $table->id();
                $table->string('document_code', 80)->unique();
                $table->date('document_date')->index();
                $table->string('document_type', 30)->index();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
                $table->foreignId('location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
                $table->string('movement_type', 50);
                $table->decimal('quantity', 15, 2);
                $table->string('description')->nullable();
                $table->foreignId('stock_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('back_office_stock_documents');
        Schema::dropIfExists('back_office_employee_advances');
        Schema::dropIfExists('back_office_cash_transactions');
        Schema::dropIfExists('back_office_cost_categories');
        Schema::dropIfExists('back_office_cash_accounts');
    }
};

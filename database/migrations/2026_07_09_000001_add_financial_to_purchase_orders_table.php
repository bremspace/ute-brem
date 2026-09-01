<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'payment_method')) {
                $table->string('payment_method', 30)->default('cash')->after('total_price');
            }
            if (!Schema::hasColumn('purchase_orders', 'credit_term_days')) {
                $table->unsignedInteger('credit_term_days')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('purchase_orders', 'credit_due_at')) {
                $table->date('credit_due_at')->nullable()->after('credit_term_days');
            }
            if (!Schema::hasColumn('purchase_orders', 'credit_status')) {
                $table->string('credit_status', 20)->default('paid')->after('credit_due_at')->index();
            }
            if (!Schema::hasColumn('purchase_orders', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('credit_status');
            }
        });

        Schema::table('back_office_cash_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('back_office_cash_transactions', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('customer_id')->constrained('suppliers')->nullOnDelete();
            }
        });

        if (!Schema::hasTable('purchase_order_payments')) {
            Schema::create('purchase_order_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->dateTime('payment_at')->index();
                $table->decimal('amount', 15, 2);
                $table->string('payment_method', 30)->default('cash');
                $table->string('reference', 120)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_payments');

        Schema::table('back_office_cash_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('back_office_cash_transactions', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['payment_method', 'credit_term_days', 'credit_due_at', 'credit_status', 'paid_amount'] as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

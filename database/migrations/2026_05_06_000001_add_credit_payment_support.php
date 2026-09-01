<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales')) {
            if (DB::getDriverName() === 'mysql' && Schema::hasColumn('sales', 'payment_method')) {
                DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('cash','transfer','qris','tempo') DEFAULT 'cash'");
            }

            Schema::table('sales', function (Blueprint $table) {
                if (! Schema::hasColumn('sales', 'credit_term_days')) {
                    $table->unsignedInteger('credit_term_days')->nullable()->after('payment_reference');
                }
                if (! Schema::hasColumn('sales', 'credit_due_at')) {
                    $table->date('credit_due_at')->nullable()->after('credit_term_days');
                }
                if (! Schema::hasColumn('sales', 'credit_status')) {
                    $table->string('credit_status', 20)->default('paid')->after('credit_due_at')->index();
                }
            });
        }

        if (Schema::hasTable('service_transactions')) {
            Schema::table('service_transactions', function (Blueprint $table) {
                if (! Schema::hasColumn('service_transactions', 'credit_term_days')) {
                    $table->unsignedInteger('credit_term_days')->nullable()->after('payment_reference');
                }
                if (! Schema::hasColumn('service_transactions', 'credit_due_at')) {
                    $table->date('credit_due_at')->nullable()->after('credit_term_days');
                }
                if (! Schema::hasColumn('service_transactions', 'credit_status')) {
                    $table->string('credit_status', 20)->default('paid')->after('credit_due_at')->index();
                }
            });
        }

        if (! Schema::hasTable('transaction_payments')) {
            Schema::create('transaction_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sale_id')->nullable()->constrained('sales')->cascadeOnDelete();
                $table->foreignId('service_transaction_id')->nullable()->constrained('service_transactions')->cascadeOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->dateTime('payment_at')->index();
                $table->decimal('amount', 15, 2);
                $table->string('payment_method', 30)->default('cash');
                $table->string('reference', 120)->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['customer_id', 'payment_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_payments');

        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) {
                foreach (['credit_status', 'credit_due_at', 'credit_term_days'] as $column) {
                    if (Schema::hasColumn('sales', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });

            if (DB::getDriverName() === 'mysql' && Schema::hasColumn('sales', 'payment_method')) {
                DB::table('sales')->where('payment_method', 'tempo')->update(['payment_method' => 'cash']);
                DB::statement("ALTER TABLE sales MODIFY payment_method ENUM('cash','transfer','qris') DEFAULT 'cash'");
            }
        }

        if (Schema::hasTable('service_transactions')) {
            Schema::table('service_transactions', function (Blueprint $table) {
                foreach (['credit_status', 'credit_due_at', 'credit_term_days'] as $column) {
                    if (Schema::hasColumn('service_transactions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};

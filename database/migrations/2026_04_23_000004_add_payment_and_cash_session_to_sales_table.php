<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'transfer', 'qris'])->default('cash')->after('change_amount');
            }

            if (! Schema::hasColumn('sales', 'payment_reference')) {
                $table->string('payment_reference', 120)->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('sales', 'cash_session_id')) {
                $table->foreignId('cash_session_id')->nullable()->after('cashier_id')->constrained('cash_sessions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'cash_session_id')) {
                $table->dropConstrainedForeignId('cash_session_id');
            }
            if (Schema::hasColumn('sales', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
            if (Schema::hasColumn('sales', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};


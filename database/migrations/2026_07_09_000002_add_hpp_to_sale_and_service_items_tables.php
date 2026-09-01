<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->default(0)->after('unit_price');
            }
        });

        Schema::table('service_transaction_items', function (Blueprint $table) {
            if (!Schema::hasColumn('service_transaction_items', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->default(0)->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_transaction_items', function (Blueprint $table) {
            if (Schema::hasColumn('service_transaction_items', 'purchase_price')) {
                $table->dropColumn('purchase_price');
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'purchase_price')) {
                $table->dropColumn('purchase_price');
            }
        });
    }
};

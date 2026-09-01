<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sales') && ! Schema::hasColumn('sales', 'sale_channel')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('sale_channel', 20)->default('toko')->after('sale_code')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'sale_channel')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('sale_channel');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('service_transactions') && ! Schema::hasColumn('service_transactions', 'device_lock_type')) {
            Schema::table('service_transactions', function (Blueprint $table) {
                $table->string('device_lock_type', 20)->nullable()->after('serial_number');
                $table->string('device_lock_value')->nullable()->after('device_lock_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('service_transactions') && Schema::hasColumn('service_transactions', 'device_lock_type')) {
            Schema::table('service_transactions', function (Blueprint $table) {
                $table->dropColumn(['device_lock_type', 'device_lock_value']);
            });
        }
    }
};

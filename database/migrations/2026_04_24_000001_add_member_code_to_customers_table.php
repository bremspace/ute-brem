<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (Schema::hasColumn('customers', 'member_code')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->string('member_code', 50)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        if (! Schema::hasColumn('customers', 'member_code')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['member_code']);
            $table->dropColumn('member_code');
        });
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'quality')) {
                $table->string('quality', 100)->nullable()->after('product_maker_id');
            }

            if (! Schema::hasColumn('products', 'is_member_only')) {
                $table->boolean('is_member_only')->default(false)->after('is_published');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'is_member_only')) {
                $table->dropColumn('is_member_only');
            }

            if (Schema::hasColumn('products', 'quality')) {
                $table->dropColumn('quality');
            }
        });
    }
};

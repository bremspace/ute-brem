<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('sub_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('sub_category_id')->constrained('brands')->nullOnDelete();
            $table->foreignId('product_maker_id')->nullable()->after('brand_id')->constrained('product_makers')->nullOnDelete();
            $table->foreignId('default_location_id')->nullable()->after('product_maker_id')->constrained('locations')->nullOnDelete();
            $table->foreignId('default_location_rack_id')->nullable()->after('default_location_id')->constrained('location_racks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('default_location_rack_id');
            $table->dropConstrainedForeignId('default_location_id');
            $table->dropConstrainedForeignId('product_maker_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('sub_category_id');
        });
    }
};

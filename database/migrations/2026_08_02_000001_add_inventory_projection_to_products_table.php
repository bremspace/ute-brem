<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'adu')) {
                $table->decimal('adu', 15, 4)->default(0)->after('stock_max'); // Average Daily Usage
            }
            if (! Schema::hasColumn('products', 'lead_time_days')) {
                $table->unsignedInteger('lead_time_days')->default(7)->after('adu'); // lead time supplier (hari)
            }
            if (! Schema::hasColumn('products', 'safety_stock')) {
                $table->decimal('safety_stock', 15, 4)->default(0)->after('lead_time_days'); // Safety Stock = ADU * 3
            }
            if (! Schema::hasColumn('products', 'rop')) {
                $table->decimal('rop', 15, 4)->default(0)->after('safety_stock'); // Reorder Point = (ADU*lead)+safety
            }
            if (! Schema::hasColumn('products', 'abc_class')) {
                $table->string('abc_class', 2)->default('C')->after('rop'); // A (Fast) | B (Medium) | C (Slow)
            }
            if (! Schema::hasColumn('products', 'last_projection_at')) {
                $table->timestamp('last_projection_at')->nullable()->after('abc_class'); // kapan proyeksi terakhir dihitung
            }
        });

        // Tabel penelusuran serial/IMEI per product (Serialized & Bin Tracking)
        if (! Schema::hasTable('item_serials')) {
            Schema::create('item_serials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->foreignId('product_stock_id')->nullable()->constrained('product_stocks')->nullOnDelete();
                $table->foreignId('location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
                $table->string('serial_number', 120)->index();
                $table->string('status', 20)->default('available')->index(); // available | reserved | sold
                $table->string('reference_type', 40)->nullable();
                $table->string('reference_code', 120)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['product_id', 'serial_number']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_serials');
        Schema::table('products', function (Blueprint $table) {
            foreach (['adu', 'lead_time_days', 'safety_stock', 'rop', 'abc_class', 'last_projection_at'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

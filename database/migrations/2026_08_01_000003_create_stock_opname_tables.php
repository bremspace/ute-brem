<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_opnames')) {
            Schema::create('stock_opnames', function (Blueprint $table) {
                $table->id();
                $table->string('opname_code', 40)->unique();
                $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
                $table->date('opname_date')->index();
                $table->string('status', 20)->default('open')->index(); // open | completed
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stock_opname_items')) {
            Schema::create('stock_opname_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->decimal('system_qty', 15, 2)->default(0);
                $table->decimal('actual_qty', 15, 2)->default(0);
                $table->decimal('difference', 15, 2)->default(0);
                $table->string('note')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
    }
};

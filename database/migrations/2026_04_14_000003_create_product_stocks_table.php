<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('damaged_quantity', 14, 2)->default(0);
            $table->decimal('stock_min', 14, 2)->nullable();
            $table->decimal('stock_max', 14, 2)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};

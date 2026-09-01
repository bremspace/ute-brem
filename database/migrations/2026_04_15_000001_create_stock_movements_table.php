<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
            $table->string('movement_type', 50);
            $table->decimal('quantity', 14, 2);
            $table->decimal('good_delta', 14, 2)->default(0);
            $table->decimal('damaged_delta', 14, 2)->default(0);
            $table->decimal('stock_before', 14, 2)->default(0);
            $table->decimal('stock_after', 14, 2)->default(0);
            $table->decimal('damaged_before', 14, 2)->default(0);
            $table->decimal('damaged_after', 14, 2)->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_code', 100)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamp('movement_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'movement_at']);
            $table->index(['location_id', 'movement_at']);
            $table->index(['reference_type', 'reference_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};

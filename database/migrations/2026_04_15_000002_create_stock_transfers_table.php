<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('transfer_code', 100)->unique();
            $table->foreignId('source_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('source_location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
            $table->foreignId('target_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('target_location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
            $table->decimal('quantity', 14, 2);
            $table->string('notes', 500)->nullable();
            $table->timestamp('transferred_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'transferred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_code', 100)->unique();
            $table->foreignId('source_location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('target_location_id')->constrained('locations')->restrictOnDelete();
            $table->string('status', 50)->default('draft'); // draft, in_transit, completed, cancelled
            $table->string('notes', 500)->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('branch_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_transfer_id')->constrained('branch_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('source_location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
            $table->foreignId('target_location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
            $table->decimal('quantity_sent', 14, 2);
            $table->decimal('quantity_received', 14, 2)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_transfer_items');
        Schema::dropIfExists('branch_transfers');
    }
};

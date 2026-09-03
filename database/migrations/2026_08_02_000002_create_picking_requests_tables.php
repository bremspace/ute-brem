<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('picking_requests')) {
            Schema::create('picking_requests', function (Blueprint $table) {
                $table->id();
                $table->string('request_code', 40)->unique();
                $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
                $table->string('status', 20)->default('open')->index(); // open | fulfilled | cancelled
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('picking_request_items')) {
            Schema::create('picking_request_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('picking_request_id')->constrained('picking_requests')->cascadeOnDelete();
                $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
                $table->foreignId('location_rack_id')->nullable()->constrained('location_racks')->nullOnDelete();
                $table->decimal('qty_requested', 15, 2)->default(0);
                $table->decimal('qty_picked', 15, 2)->default(0);
                $table->string('status', 20)->default('requested'); // requested | reserved | picked | cancelled
                $table->string('note')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('picking_request_items');
        Schema::dropIfExists('picking_requests');
    }
};

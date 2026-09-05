<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('delivery_trackings')) {
            Schema::create('delivery_trackings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('online_order_id')->constrained('online_orders')->cascadeOnDelete();
                $table->string('provider', 50);
                $table->string('tracking_number', 100)->nullable();
                $table->string('status', 50);
                $table->string('location', 255)->nullable();
                $table->decimal('lat', 10, 8)->nullable();
                $table->decimal('lng', 11, 8)->nullable();
                $table->text('notes')->nullable();
                $table->json('raw_data')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_trackings');
    }
};

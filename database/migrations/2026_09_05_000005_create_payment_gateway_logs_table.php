<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_gateway_logs')) {
            Schema::create('payment_gateway_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('online_order_id')->constrained('online_orders')->cascadeOnDelete();
                $table->string('gateway', 50);
                $table->string('gateway_order_id', 100)->nullable();
                $table->string('gateway_payment_id', 100)->nullable();
                $table->string('payment_type', 50)->nullable();
                $table->decimal('amount', 12, 2);
                $table->string('status', 50);
                $table->json('raw_response')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_logs');
    }
};

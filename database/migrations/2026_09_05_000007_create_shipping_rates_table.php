<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shipping_rates')) {
            Schema::create('shipping_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shipping_provider_id')->constrained('shipping_providers')->cascadeOnDelete();
                $table->string('origin_city', 100);
                $table->string('destination_city', 100);
                $table->decimal('min_weight', 8, 2)->default(0);
                $table->decimal('max_weight', 8, 2)->default(9999);
                $table->decimal('base_rate', 12, 2);
                $table->decimal('per_kg_rate', 12, 2)->default(0);
                $table->string('estimated_days', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};

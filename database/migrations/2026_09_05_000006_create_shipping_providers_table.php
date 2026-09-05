<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shipping_providers')) {
            Schema::create('shipping_providers', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 50)->unique();
                $table->enum('type', ['instant', 'same_day', 'express', 'regular', 'economy']);
                $table->boolean('is_active')->default(true);
                $table->json('api_config')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_providers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('supplier_product_code', 100)->nullable();
            $table->decimal('last_purchase_price', 15, 2)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_suppliers');
    }
};

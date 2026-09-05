<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
                $table->string('session_id', 100);
                $table->enum('status', ['active', 'abandoned', 'converted'])->default('active');
                $table->timestamps();

                $table->index('session_id');
                $table->index('customer_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

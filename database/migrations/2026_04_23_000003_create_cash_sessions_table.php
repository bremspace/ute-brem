<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cash_sessions')) {
            return;
        }

        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('session_date')->index(); // local date (Asia/Jakarta)
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->restrictOnDelete();

            $table->decimal('opening_cash', 15, 2)->default(0);
            $table->decimal('closing_cash', 15, 2)->nullable();

            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['session_date', 'location_id', 'cashier_id'], 'cash_sessions_unique_date_location_cashier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};


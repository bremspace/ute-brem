<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('action');
            $table->string('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('target_user_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['user_id', 'created_at']);
            $table->index(['target_user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });

        Schema::create('table_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('table_key', 100);
            $table->json('preferences');
            $table->timestamps();

            $table->unique(['user_id', 'table_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_preferences');
        Schema::dropIfExists('user_logs');
    }
};

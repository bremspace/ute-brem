<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sid_retail_import_logs')) {
            Schema::create('sid_retail_import_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sid_retail_config_id')->constrained('sid_retail_config')->cascadeOnDelete();
                $table->string('table_name', 100);
                $table->enum('status', ['pending', 'running', 'completed', 'failed', 'partial'])->default('pending');
                $table->unsignedInteger('records_total')->default(0);
                $table->unsignedInteger('records_imported')->default(0);
                $table->unsignedInteger('records_failed')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sid_retail_import_logs');
    }
};
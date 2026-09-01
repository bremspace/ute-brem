<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_point_ledgers')) {
            return;
        }

        if (Schema::hasTable('customer_point_ledger_files')) {
            return;
        }

        Schema::create('customer_point_ledger_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_point_ledger_id')
                ->constrained('customer_point_ledgers')
                ->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_point_ledger_files');
    }
};


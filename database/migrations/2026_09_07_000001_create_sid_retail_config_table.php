<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sid_retail_config')) {
            Schema::create('sid_retail_config', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('host', 100);
                $table->string('port', 10)->default('3306');
                $table->string('database');
                $table->string('username');
                $table->string('password');
                $table->string('prefix', 20)->default('');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sid_retail_config');
    }
};
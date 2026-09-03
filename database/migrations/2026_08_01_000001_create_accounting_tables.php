<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bagan Akun (Chart of Accounts) — standard double-entry
        if (! Schema::hasTable('chart_of_accounts')) {
            Schema::create('chart_of_accounts', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name');
                // normal_balance: debit | credit
                $table->enum('normal_balance', ['debit', 'credit'])->default('debit');
                // category: asset | liability | equity | revenue | expense
                $table->string('category', 20)->index();
                $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
                $table->string('type', 30)->nullable(); // cash, bank, receivable, inventory, payable, payroll, cogs, other
                $table->decimal('opening_balance', 15, 2)->default(0);
                $table->boolean('is_postable')->default(true);
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // Jurnal Umum header
        if (! Schema::hasTable('journal_entries')) {
            Schema::create('journal_entries', function (Blueprint $table) {
                $table->id();
                $table->string('journal_code', 40)->unique();
                $table->date('journal_date')->index();
                $table->string('source_type', 40)->index();   // sale, sale_payment, purchase, purchase_payment, cash, mutation, kasbon, service, sales_void, ...
                $table->foreignId('source_id')->nullable();
                $table->string('source_reference', 120)->nullable()->index(); // sale_code / po_number / etc
                $table->string('description', 255)->nullable();
                $table->decimal('debit_total', 15, 2)->default(0);
                $table->decimal('credit_total', 15, 2)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['source_type', 'source_id']);
            });
        }

        // Baris jurnal (debit/kredit per akun)
        if (! Schema::hasTable('journal_entry_lines')) {
            Schema::create('journal_entry_lines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
                $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
                $table->decimal('debit', 15, 2)->default(0);
                $table->decimal('credit', 15, 2)->default(0);
                $table->string('memo', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('chart_of_accounts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('back_office_cash_accounts', 'chart_of_account_id')) {
            Schema::table('back_office_cash_accounts', function (Blueprint $table) {
                $table->foreignId('chart_of_account_id')->nullable()->after('current_balance')
                    ->constrained('chart_of_accounts')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('back_office_cash_accounts', 'chart_of_account_id')) {
            Schema::table('back_office_cash_accounts', function (Blueprint $table) {
                $table->dropForeign(['chart_of_account_id']);
                $table->dropColumn('chart_of_account_id');
            });
        }
    }
};

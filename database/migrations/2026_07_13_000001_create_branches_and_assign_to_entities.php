<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create branches table
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_main')->default(false);
            $table->string('phone')->nullable();
            $table->string('address', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Insert default branches (Pusat and Cabang 1)
        $mainBranchId = DB::table('branches')->insertGetId([
            'name' => 'Cabang Utama (Pusat)',
            'code' => 'HO',
            'is_main' => true,
            'phone' => '021-123456',
            'address' => 'Jl. Pusat No. 1, Jakarta',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('branches')->insert([
            'name' => 'Cabang Surabaya',
            'code' => 'SBY',
            'is_main' => false,
            'phone' => '031-654321',
            'address' => 'Jl. Surabaya No. 99, Surabaya',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Add branch_id to locations table
        Schema::table('locations', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
        });

        // Link existing locations to main branch
        DB::table('locations')->update(['branch_id' => $mainBranchId]);

        // 4. Add branch_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
        });

        // Link existing users to main branch
        DB::table('users')->update(['branch_id' => $mainBranchId]);

        // 5. Add source/target branch_id to branch_transfers table
        Schema::table('branch_transfers', function (Blueprint $table) {
            $table->foreignId('source_branch_id')->nullable()->after('transfer_code')->constrained('branches')->nullOnDelete();
            $table->foreignId('target_branch_id')->nullable()->after('source_branch_id')->constrained('branches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('branch_transfers', function (Blueprint $table) {
            $table->dropForeign(['source_branch_id']);
            $table->dropColumn('source_branch_id');
            $table->dropForeign(['target_branch_id']);
            $table->dropColumn('target_branch_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::dropIfExists('branches');
    }
};

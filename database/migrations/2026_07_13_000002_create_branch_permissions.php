<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Matikan pengecekan foreign key untuk mencegah eror Integrity Constraint Violation
        Schema::disableForeignKeyConstraints();

        // 1. Pastikan parent 'master.access' tersedia di tabel permissions
        DB::table('permissions')->updateOrInsert(
            ['name' => 'master.access'],
            [
                'display_name' => 'Master - Access',
                'description'  => 'Master access module',
                'module'       => 'master',
                'parent'       => null,
                'sort_order'   => 20,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]
        );

        // 2. Daftar child permissions
        $permissions = [
            [
                'name'         => 'master.branches.view',
                'display_name' => 'Branches - View',
                'description'  => 'Can view branches',
                'module'       => 'master',
                'parent'       => 'master.access',
                'sort_order'   => 21,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'name'         => 'master.branches.create',
                'display_name' => 'Branches - Create',
                'description'  => 'Can create branches',
                'module'       => 'master',
                'parent'       => 'master.access',
                'sort_order'   => 22,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'name'         => 'master.branches.edit',
                'display_name' => 'Branches - Edit',
                'description'  => 'Can edit branches',
                'module'       => 'master',
                'parent'       => 'master.access',
                'sort_order'   => 23,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'name'         => 'master.branches.delete',
                'display_name' => 'Branches - Delete',
                'description'  => 'Can delete branches',
                'module'       => 'master',
                'parent'       => 'master.access',
                'sort_order'   => 24,
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }

        // 3. Assign permissions ke role 'admin'
        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permission) {
                $permId = DB::table('permissions')->where('name', $permission['name'])->value('id');
                if ($permId) {
                    DB::table('role_permission')->updateOrInsert(
                        ['role_id' => $adminRole->id, 'permission_id' => $permId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }

        // Aktifkan kembali pengecekan foreign key
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        $names = ['master.branches.view', 'master.branches.create', 'master.branches.edit', 'master.branches.delete'];
        
        $permIds = DB::table('permissions')->whereIn('name', $names)->pluck('id');
        DB::table('role_permission')->whereIn('permission_id', $permIds)->delete();
        DB::table('permissions')->whereIn('name', $names)->delete();

        Schema::enableForeignKeyConstraints();
    }
};
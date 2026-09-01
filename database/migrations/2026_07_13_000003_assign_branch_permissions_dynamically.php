<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = [
            'master.locations.view' => 'master.branches.view',
            'master.locations.create' => 'master.branches.create',
            'master.locations.edit' => 'master.branches.edit',
            'master.locations.delete' => 'master.branches.delete',
        ];

        foreach ($mapping as $locationPermName => $branchPermName) {
            $locationPermId = DB::table('permissions')->where('name', $locationPermName)->value('id');
            $branchPermId = DB::table('permissions')->where('name', $branchPermName)->value('id');

            if ($locationPermId && $branchPermId) {
                // Find all roles that have this location permission
                $roleIds = DB::table('role_permission')
                    ->where('permission_id', $locationPermId)
                    ->pluck('role_id');

                foreach ($roleIds as $roleId) {
                    DB::table('role_permission')->updateOrInsert(
                        ['role_id' => $roleId, 'permission_id' => $branchPermId],
                        ['created_at' => now(), 'updated_at' => now()]
                    );
                }
            }
        }
    }

    public function down(): void
    {
        // No-op
    }
};

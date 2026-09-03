<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'accounting.access'],
            [
                'display_name' => 'Akuntansi - Akses',
                'description' => 'Can access accounting / pembukuan module',
                'module' => 'accounting',
                'is_active' => true,
            ]
        );

        // Attach ke template super admin & admin (role-template yg sudah dipakai owner/admin)
        $roleNames = ['super_admin_template', 'admin_template', 'super_admin', 'admin'];
        foreach (Role::whereIn('name', $roleNames)->get() as $role) {
            if (! $role->permissions()->where('permissions.id', $permission->id)->exists()) {
                $role->permissions()->attach($permission->id);
            }
        }
    }

    public function down(): void
    {
        // data migration — tidak di-reverse agar tidak menghapus permission yang mungkin terpakai
    }
};

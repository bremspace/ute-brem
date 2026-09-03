<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        $this->command->info('Clearing existing roles, permissions, and user assignments...');

        // Disable foreign key checks temporarily
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        \DB::table('user_role')->delete();
        \DB::table('role_permission')->delete();
        Role::truncate();
        Permission::truncate();

        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create permissions following menu hierarchy structure
        $this->command->info('Creating permissions...');
        $permissions = [

            // === MANAGEMENT (Header) ===
            ['name' => 'management.access', 'display_name' => 'Management', 'description' => 'Can access Management section', 'module' => 'management', 'parent' => null, 'sort_order' => 2],

            // Management > Users
            ['name' => 'management.users.view', 'display_name' => 'Users - View', 'description' => 'Can view users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 1],
            ['name' => 'management.users.create', 'display_name' => 'Users - Create', 'description' => 'Can create users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 2],
            ['name' => 'management.users.edit', 'display_name' => 'Users - Edit', 'description' => 'Can edit users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 3],
            ['name' => 'management.users.delete', 'display_name' => 'Users - Delete', 'description' => 'Can delete users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 4],
            ['name' => 'management.users.restore', 'display_name' => 'Users - Restore', 'description' => 'Can restore deleted users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 5],
            ['name' => 'management.users.force_delete', 'display_name' => 'Users - Force Delete', 'description' => 'Can permanently delete users', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 6],
            ['name' => 'management.users.logs', 'display_name' => 'Users - View Logs', 'description' => 'Can view user activity logs', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 7],
            ['name' => 'management.users.permissions', 'display_name' => 'Users - Manage Permissions', 'description' => 'Can manage user permissions directly', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 8],

            // Management > Roles (Templates)
            ['name' => 'management.roles.view', 'display_name' => 'Role Templates - View', 'description' => 'Can view role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 9],
            ['name' => 'management.roles.create', 'display_name' => 'Role Templates - Create', 'description' => 'Can create role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 10],
            ['name' => 'management.roles.edit', 'display_name' => 'Role Templates - Edit', 'description' => 'Can edit role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 11],
            ['name' => 'management.roles.delete', 'display_name' => 'Role Templates - Delete', 'description' => 'Can delete role templates', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 12],
            ['name' => 'management.settings.printer', 'display_name' => 'POS - Printer Settings', 'description' => 'Can manage POS printer settings', 'module' => 'management', 'parent' => 'management.access', 'sort_order' => 13],

            // === IMAGES ===
            ['name' => 'images.access', 'display_name' => 'Images', 'description' => 'Can access Images section', 'module' => 'images', 'parent' => null, 'sort_order' => 3],
            ['name' => 'images.view', 'display_name' => 'Images - View', 'description' => 'Can view images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 1],
            ['name' => 'images.create', 'display_name' => 'Images - Upload', 'description' => 'Can upload images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 2],
            ['name' => 'images.edit', 'display_name' => 'Images - Edit', 'description' => 'Can edit images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 3],
            ['name' => 'images.delete', 'display_name' => 'Images - Delete', 'description' => 'Can delete images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 4],
            ['name' => 'images.restore', 'display_name' => 'Images - Restore', 'description' => 'Can restore deleted images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 5],
            ['name' => 'images.force_delete', 'display_name' => 'Images - Force Delete', 'description' => 'Can permanently delete images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 6],

            // === MASTER DATA (Header) ===
            ['name' => 'master.access', 'display_name' => 'Master Data', 'description' => 'Can access Master Data section', 'module' => 'master', 'parent' => null, 'sort_order' => 4],

            // Master > Categories
            ['name' => 'master.categories.view', 'display_name' => 'Categories - View', 'description' => 'Can view categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 1],
            ['name' => 'master.categories.create', 'display_name' => 'Categories - Create', 'description' => 'Can create categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 2],
            ['name' => 'master.categories.edit', 'display_name' => 'Categories - Edit', 'description' => 'Can edit categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 3],
            ['name' => 'master.categories.delete', 'display_name' => 'Categories - Delete', 'description' => 'Can delete categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 4],
            ['name' => 'master.categories.restore', 'display_name' => 'Categories - Restore', 'description' => 'Can restore deleted categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 5],
            ['name' => 'master.categories.force_delete', 'display_name' => 'Categories - Force Delete', 'description' => 'Can permanently delete categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 6],

            // Master > Products
            ['name' => 'master.products.view', 'display_name' => 'Products - View', 'description' => 'Can view products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 7],
            ['name' => 'master.products.create', 'display_name' => 'Products - Create', 'description' => 'Can create products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 8],
            ['name' => 'master.products.edit', 'display_name' => 'Products - Edit', 'description' => 'Can edit products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 9],
            ['name' => 'master.products.delete', 'display_name' => 'Products - Delete', 'description' => 'Can delete products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 10],
            ['name' => 'master.products.restore', 'display_name' => 'Products - Restore', 'description' => 'Can restore deleted products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 11],
            ['name' => 'master.products.force_delete', 'display_name' => 'Products - Force Delete', 'description' => 'Can permanently delete products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 12],

            // Master > Sub Categories
            ['name' => 'master.sub_categories.view', 'display_name' => 'Sub Categories - View', 'description' => 'Can view sub categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 13],
            ['name' => 'master.sub_categories.create', 'display_name' => 'Sub Categories - Create', 'description' => 'Can create sub categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 14],
            ['name' => 'master.sub_categories.edit', 'display_name' => 'Sub Categories - Edit', 'description' => 'Can edit sub categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 15],
            ['name' => 'master.sub_categories.delete', 'display_name' => 'Sub Categories - Delete', 'description' => 'Can delete sub categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 16],

            // Master > Brands
            ['name' => 'master.brands.view', 'display_name' => 'Brands - View', 'description' => 'Can view brands', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 17],
            ['name' => 'master.brands.create', 'display_name' => 'Brands - Create', 'description' => 'Can create brands', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 18],
            ['name' => 'master.brands.edit', 'display_name' => 'Brands - Edit', 'description' => 'Can edit brands', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 19],
            ['name' => 'master.brands.delete', 'display_name' => 'Brands - Delete', 'description' => 'Can delete brands', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 20],

            // Master > Product Types
            ['name' => 'master.product_types.view', 'display_name' => 'Product Types - View', 'description' => 'Can view product types', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 21],
            ['name' => 'master.product_types.create', 'display_name' => 'Product Types - Create', 'description' => 'Can create product types', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 22],
            ['name' => 'master.product_types.edit', 'display_name' => 'Product Types - Edit', 'description' => 'Can edit product types', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 23],
            ['name' => 'master.product_types.delete', 'display_name' => 'Product Types - Delete', 'description' => 'Can delete product types', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 24],

            // Master > Locations
            ['name' => 'master.locations.view', 'display_name' => 'Locations - View', 'description' => 'Can view locations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 25],
            ['name' => 'master.locations.create', 'display_name' => 'Locations - Create', 'description' => 'Can create locations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 26],
            ['name' => 'master.locations.edit', 'display_name' => 'Locations - Edit', 'description' => 'Can edit locations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 27],
            ['name' => 'master.locations.delete', 'display_name' => 'Locations - Delete', 'description' => 'Can delete locations', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 28],

            // Master > Branches
            ['name' => 'master.branches.view', 'display_name' => 'Branches - View', 'description' => 'Can view branches', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 29],
            ['name' => 'master.branches.create', 'display_name' => 'Branches - Create', 'description' => 'Can create branches', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 30],
            ['name' => 'master.branches.edit', 'display_name' => 'Branches - Edit', 'description' => 'Can edit branches', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 31],
            ['name' => 'master.branches.delete', 'display_name' => 'Branches - Delete', 'description' => 'Can delete branches', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 32],

            // Master > Suppliers
            ['name' => 'master.suppliers.view', 'display_name' => 'Suppliers - View', 'description' => 'Can view suppliers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 29],
            ['name' => 'master.suppliers.create', 'display_name' => 'Suppliers - Create', 'description' => 'Can create suppliers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 30],
            ['name' => 'master.suppliers.edit', 'display_name' => 'Suppliers - Edit', 'description' => 'Can edit suppliers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 31],
            ['name' => 'master.suppliers.delete', 'display_name' => 'Suppliers - Delete', 'description' => 'Can delete suppliers', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 32],

            // Master > Customer Groups
            ['name' => 'master.customer_groups.view', 'display_name' => 'Customer Groups - View', 'description' => 'Can view customer groups', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 33],
            ['name' => 'master.customer_groups.create', 'display_name' => 'Customer Groups - Create', 'description' => 'Can create customer groups', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 34],
            ['name' => 'master.customer_groups.edit', 'display_name' => 'Customer Groups - Edit', 'description' => 'Can edit customer groups', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 35],
            ['name' => 'master.customer_groups.delete', 'display_name' => 'Customer Groups - Delete', 'description' => 'Can delete customer groups', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 36],

            // Master > Product Stocks
            ['name' => 'master.product_stocks.view', 'display_name' => 'Product Stocks - View', 'description' => 'Can view stock by product location', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 37],
            ['name' => 'master.product_stocks.edit', 'display_name' => 'Product Stocks - Edit', 'description' => 'Can update stock by product location', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 38],

            // Master > Employees
            ['name' => 'master.employees.view', 'display_name' => 'Employees - View', 'description' => 'Can view employees', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 39],
            ['name' => 'master.employees.sync', 'display_name' => 'Employees - Sync', 'description' => 'Can sync employees from JPayroll', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 40],

            // === TRANSAKSI ===
            ['name' => 'transactions.view', 'display_name' => 'Transaksi - View', 'description' => 'Can view transactions', 'module' => 'transactions', 'parent' => null, 'sort_order' => 5],
            ['name' => 'transactions.create', 'display_name' => 'Transaksi - Create', 'description' => 'Can create / sell transactions', 'module' => 'transactions', 'parent' => null, 'sort_order' => 6],
            ['name' => 'transactions.settings', 'display_name' => 'Transaksi - Settings', 'description' => 'Can manage member point settings', 'module' => 'transactions', 'parent' => null, 'sort_order' => 7],

            // === AKUNTANSI (Pembukuan Double-Entry) ===
            ['name' => 'accounting.access', 'display_name' => 'Akuntansi - Akses', 'description' => 'Can access accounting / pembukuan module', 'module' => 'accounting', 'parent' => null, 'sort_order' => 8],

        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create role templates for user creation
        $this->command->info('Creating role templates...');
        $roles = [
            [
                'name' => 'super_admin_template',
                'display_name' => 'Super Administrator Template',
                'description' => 'Template with full system access - all permissions',
                'permissions' => Permission::all()->pluck('name')->toArray()
            ],
            [
                'name' => 'admin_template',
                'display_name' => 'Administrator Template',
                'description' => 'Template with management access and most features',
                'permissions' => [
                    'dashboard.view',
                    'management.access',
                    'management.users.view',
                    'management.users.create',
                    'management.users.edit',
                    'management.users.delete',
                    'management.users.restore',
                    'management.users.logs',
                    'management.roles.view',
                    'management.roles.create',
                    'management.roles.edit',
                    'management.settings.printer',
                    'images.access',
                    'images.view',
                    'images.create',
                    'images.edit',
                    'images.delete',
                    'images.restore',
                    'master.access',
                    'master.categories.view',
                    'master.categories.create',
                    'master.categories.edit',
                    'master.categories.delete',
                    'master.categories.restore',
                    'master.products.view',
                    'master.products.create',
                    'master.products.edit',
                    'master.products.delete',
                    'master.products.restore',
                    'master.sub_categories.view',
                    'master.sub_categories.create',
                    'master.sub_categories.edit',
                    'master.sub_categories.delete',
                    'master.brands.view',
                    'master.brands.create',
                    'master.brands.edit',
                    'master.brands.delete',
                    'master.product_types.view',
                    'master.product_types.create',
                    'master.product_types.edit',
                    'master.product_types.delete',
                    'master.locations.view',
                    'master.locations.create',
                    'master.locations.edit',
                    'master.locations.delete',
                    'master.branches.view',
                    'master.branches.create',
                    'master.branches.edit',
                    'master.branches.delete',
                    'master.suppliers.view',
                    'master.suppliers.create',
                    'master.suppliers.edit',
                    'master.suppliers.delete',
                    'master.customer_groups.view',
                    'master.customer_groups.create',
                    'master.customer_groups.edit',
                    'master.customer_groups.delete',
                    'master.product_stocks.view',
                    'master.product_stocks.edit',
                    'master.employees.view',
                    'master.employees.sync',
                    'transactions.view',
                    'transactions.create',
                    'transactions.settings',
                    'accounting.access',
                ]
            ],
            [
                'name' => 'user_manager_template',
                'display_name' => 'User Manager Template',
                'description' => 'Template for user management focus',
                'permissions' => [
                    'dashboard.view',
                    'management.access',
                    'management.users.view',
                    'management.users.create',
                    'management.users.edit',
                    'management.users.logs',
                    'management.roles.view',
                ]
            ],
            [
                'name' => 'viewer_template',
                'display_name' => 'Viewer Template',
                'description' => 'Template for read-only access',
                'permissions' => [
                    'dashboard.view',
                    'management.access',
                    'management.users.view',
                    'master.access',
                    'master.categories.view',
                    'master.products.view',
                    'master.sub_categories.view',
                    'master.brands.view',
                    'master.product_types.view',
                    'master.locations.view',
                    'master.branches.view',
                    'master.suppliers.view',
                    'master.customer_groups.view',
                    'master.product_stocks.view',
                    'master.employees.view',
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::create([
                'name' => $roleData['name'],
                'display_name' => $roleData['display_name'],
                'description' => $roleData['description']
            ]);

            // Assign permissions to role
            foreach ($roleData['permissions'] as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$role->hasPermission($permission)) {
                    $role->assignPermission($permission);
                }
            }
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }
}

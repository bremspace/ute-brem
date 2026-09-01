<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding users...');

        $users = [
            [
                'name' => 'Owner Uteparts',
                'username' => 'owner',
                'email' => 'owner@uteparts.test',
                'password' => 'Owner123!',
                'role' => 'super_admin_template',
            ],
            [
                'name' => 'Manager Uteparts',
                'username' => 'manager',
                'email' => 'manager@uteparts.test',
                'password' => 'Manager123!',
                'role' => 'admin_template',
            ],
            [
                'name' => 'Kasir Uteparts',
                'username' => 'kasir',
                'email' => 'kasir@uteparts.test',
                'password' => 'Kasir123!',
                'role' => 'viewer_template',
            ],
        ];

        foreach ($users as $item) {
            $user = User::withTrashed()->firstWhere('username', $item['username']);

            if ($user && $user->trashed()) {
                $user->restore();
            }

            $user = User::updateOrCreate(
                ['username' => $item['username']],
                [
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'password' => Hash::make($item['password']),
                    'email_verified_at' => now(),
                ]
            );

            $role = Role::where('name', $item['role'])->first();

            if ($role) {
                DB::table('user_role')->where('user_id', $user->id)->delete();
                $user->assignRole($role);
            }
        }

        $this->command->info(count($users) . ' users seeded successfully.');
    }
}

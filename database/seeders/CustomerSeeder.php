<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $memberGroup = CustomerGroup::orderBy('sort_order')->first();
        $regularGroup = CustomerGroup::orderByDesc('sort_order')->first();

        $members = [
            ['name' => 'Andi Member', 'phone' => '081234567801', 'email' => 'andi.member@uteparts.test', 'member_code' => 'MBR-000001'],
            ['name' => 'Budi Member', 'phone' => '081234567802', 'email' => 'budi.member@uteparts.test', 'member_code' => 'MBR-000002'],
            ['name' => 'Citra Member', 'phone' => '081234567803', 'email' => 'citra.member@uteparts.test', 'member_code' => 'MBR-000003'],
            ['name' => 'Dewi Member', 'phone' => '081234567804', 'email' => 'dewi.member@uteparts.test', 'member_code' => 'MBR-000004'],
            ['name' => 'Eko Member', 'phone' => '081234567805', 'email' => 'eko.member@uteparts.test', 'member_code' => 'MBR-000005'],
        ];

        foreach ($members as $member) {
            Customer::updateOrCreate(
                ['email' => $member['email']],
                [
                    'customer_group_id' => $memberGroup?->id,
                    'member_code' => $member['member_code'] ?? null,
                    'name' => $member['name'],
                    'phone' => $member['phone'],
                    'password' => 'Member123!',
                    'type' => 'member',
                    'is_active' => true,
                ]
            );
        }

        $regulars = [
            ['name' => 'Fajar Customer', 'phone' => '089999000101', 'email' => 'fajar.customer@uteparts.test'],
            ['name' => 'Gita Customer', 'phone' => '089999000102', 'email' => 'gita.customer@uteparts.test'],
            ['name' => 'Hendra Customer', 'phone' => '089999000103', 'email' => 'hendra.customer@uteparts.test'],
            ['name' => 'Intan Customer', 'phone' => '089999000104', 'email' => 'intan.customer@uteparts.test'],
            ['name' => 'Joko Customer', 'phone' => '089999000105', 'email' => 'joko.customer@uteparts.test'],
        ];

        foreach ($regulars as $regular) {
            Customer::updateOrCreate(
                ['email' => $regular['email']],
                [
                    'customer_group_id' => $regularGroup?->id,
                    'name' => $regular['name'],
                    'phone' => $regular['phone'],
                    'password' => null,
                    'type' => 'regular',
                    'is_active' => true,
                ]
            );
        }
    }
}

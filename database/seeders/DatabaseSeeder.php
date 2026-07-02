<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin'], ['description' => 'Quản trị viên hệ thống']);
        Role::firstOrCreate(['name' => 'Staff'], ['description' => 'Nhân viên bán hàng/kho']);
        Role::firstOrCreate(['name' => 'Customer'], ['description' => 'Khách hàng mua sắm']);

        User::factory()->create([
            'role_id' => $adminRole->id,
            'fullname' => 'MilkMart Admin',
            'email' => 'admin@milkmart.local',
        ]);
    }
}

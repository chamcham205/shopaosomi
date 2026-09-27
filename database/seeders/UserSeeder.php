<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * UserSeeder
 *
 * Tạo tài khoản mẫu cho Phase 1 testing.
 * Password đều được hash bằng Hash::make() – không lưu plaintext.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ─── SUPER ADMIN ───────────────────────────────────────────
        // Có toàn quyền admin + quản lý tài khoản admin khác
        User::updateOrCreate(
            ['email' => 'superadmin@shirtstore.local'],
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@shirtstore.local',
                'phone'    => '0901000001',
                'password' => Hash::make('Admin@123'),
                'role'     => 'super_admin',
                'status'   => 'active',
                'theme'    => 'light',
            ]
        );

        // ─── ADMIN ─────────────────────────────────────────────────
        // Quản trị website, KHÔNG được mua hàng
        User::updateOrCreate(
            ['email' => 'admin@shirtstore.local'],
            [
                'name'     => 'Admin',
                'email'    => 'admin@shirtstore.local',
                'phone'    => '0901000002',
                'password' => Hash::make('Admin@123'),
                'role'     => 'admin',
                'status'   => 'active',
                'theme'    => 'light',
            ]
        );

        // ─── CUSTOMER ──────────────────────────────────────────────
        // Khách hàng thông thường, có thể mua hàng
        User::updateOrCreate(
            ['email' => 'customer@shirtstore.local'],
            [
                'name'     => 'Nguyễn Văn A',
                'email'    => 'customer@shirtstore.local',
                'phone'    => '0901000003',
                'password' => Hash::make('Customer@123'),
                'role'     => 'customer',
                'status'   => 'active',
                'theme'    => 'light',
            ]
        );

        // ─── CUSTOMER BỊ KHÓA (để test) ────────────────────────────
        User::updateOrCreate(
            ['email' => 'locked@shirtstore.local'],
            [
                'name'     => 'Tài Khoản Bị Khóa',
                'email'    => 'locked@shirtstore.local',
                'phone'    => '0901000004',
                'password' => Hash::make('Customer@123'),
                'role'     => 'customer',
                'status'   => 'locked', // Tài khoản bị khóa để test
                'theme'    => 'light',
            ]
        );

        foreach ([
            ['name' => 'Trần Minh Anh', 'email' => 'minhanh@shirtstore.local', 'phone' => '0901000005', 'theme' => 'dark'],
            ['name' => 'Lê Hoàng Nam', 'email' => 'hoangnam@shirtstore.local', 'phone' => '0901000006', 'theme' => 'system'],
        ] as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                $customer + [
                    'password' => Hash::make('Customer@123'),
                    'role' => 'customer',
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('✅ UserSeeder: Đã tạo 6 tài khoản mẫu.');
        $this->command->line('   superadmin@shirtstore.local / Admin@123 (super_admin)');
        $this->command->line('   admin@shirtstore.local      / Admin@123 (admin)');
        $this->command->line('   customer@shirtstore.local   / Customer@123 (customer)');
        $this->command->line('   locked@shirtstore.local     / Customer@123 (locked)');
        $this->command->line('   minhanh@shirtstore.local    / Customer@123 (customer)');
        $this->command->line('   hoangnam@shirtstore.local   / Customer@123 (customer)');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Chạy toàn bộ seeders theo thứ tự đúng.
     * Thứ tự quan trọng vì có FK constraints.
     */
    public function run(): void
    {
        // Phase 1: Tài khoản người dùng
        $this->call([
            UserSeeder::class,   // Phase 1 – tài khoản demo
            Phase2Seeder::class, // Phase 2 – categories, colors, sizes, products
            Phase10DemoSeeder::class, // Phase 10 – dữ liệu demo đầy đủ
        ]);
    }
}

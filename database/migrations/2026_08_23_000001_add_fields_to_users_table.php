<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Cập nhật bảng users theo DATABASE_SCHEMA.md Phase 0
 *
 * Bảng users lưu thông tin tất cả người dùng:
 * - customer: mua hàng thông thường
 * - admin: quản trị website, KHÔNG mua hàng
 * - super_admin: toàn quyền admin + quản lý admin khác
 */
return new class extends Migration
{
    public function up(): void
    {
        // Cập nhật bảng users: thêm các cột theo schema đã thiết kế
        Schema::table('users', function (Blueprint $table) {
            // Số điện thoại (nullable vì admin không cần nhập SĐT)
            $table->string('phone', 20)->nullable()->after('email');

            // Role phân quyền: customer mặc định khi đăng ký
            $table->enum('role', ['customer', 'admin', 'super_admin'])
                  ->default('customer')
                  ->after('phone');

            // Ảnh đại diện
            $table->string('avatar')->nullable()->after('role');

            // Giao diện sáng/tối
            $table->enum('theme', ['light', 'dark', 'system'])
                  ->default('light')
                  ->after('avatar');

            // Trạng thái tài khoản: locked = không được đăng nhập
            $table->enum('status', ['active', 'locked'])
                  ->default('active')
                  ->after('theme');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'avatar', 'theme', 'status']);
        });
    }
};

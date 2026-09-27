<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 – Migration: categories, colors, sizes
 * Tạo 3 bảng lookup trước vì products/variants phụ thuộc vào chúng.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── categories ─────────────────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191)->notNull();
            $table->string('slug', 191)->unique()->notNull(); // URL-friendly
            $table->string('image', 255)->nullable();         // Ảnh danh mục
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });

        // ── colors ─────────────────────────────────────────────────
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->notNull();     // VD: Trắng, Navy
            $table->string('hex_code', 7)->notNull();   // VD: #FFFFFF
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // ── sizes ──────────────────────────────────────────────────
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20)->unique()->notNull(); // S, M, L, XL, XXL
            $table->integer('sort_order')->default(0);       // S=1, M=2, ...
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('colors');
        Schema::dropIfExists('categories');
    }
};

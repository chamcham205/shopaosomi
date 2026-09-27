<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'name')) {
                $table->string('name')->nullable()->after('recipient_name');
            }
            if (!Schema::hasColumn('orders', 'total_price')) {
                $table->decimal('total_price', 15, 2)->nullable()->after('total');
            }
            if (!Schema::hasColumn('orders', 'to_district_id')) {
                $table->unsignedBigInteger('to_district_id')->nullable()->after('address');
            }
            if (!Schema::hasColumn('orders', 'to_ward_code')) {
                $table->string('to_ward_code')->nullable()->after('to_district_id');
            }
            if (!Schema::hasColumn('orders', 'ghn_total_fee')) {
                $table->decimal('ghn_total_fee', 12, 2)->nullable()->after('shipping_fee');
            }
            if (!Schema::hasColumn('orders', 'shipping_status')) {
                $table->string('shipping_status')->nullable()->default('pending')->after('status');
            }
            if (!Schema::hasColumn('orders', 'ghn_order_code')) {
                $table->string('ghn_order_code')->nullable()->after('order_code');
            }

            // Change columns to be flexible/nullable to support both old and Lab 06 formats
            $table->string('payment_method', 50)->default('cod')->change();
            $table->string('status', 50)->default('pending')->change();
            $table->string('recipient_name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('province')->nullable()->change();
            $table->string('district')->nullable()->change();
            $table->string('ward')->nullable()->change();
            $table->decimal('subtotal', 12, 2)->nullable()->change();
            $table->decimal('total', 12, 2)->nullable()->change();
            $table->uuid('checkout_token')->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('order_id')->constrained('products')->nullOnDelete();
            }
            $table->string('product_name')->nullable()->change();
            $table->string('product_sku', 100)->nullable()->change();
            $table->string('color_name')->nullable()->change();
            $table->string('size_name', 30)->nullable()->change();
            $table->decimal('subtotal', 12, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropConstrainedForeignId('product_id');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'total_price',
                'to_district_id',
                'to_ward_code',
                'ghn_total_fee',
                'shipping_status',
                'ghn_order_code',
            ]);
        });
    }
};

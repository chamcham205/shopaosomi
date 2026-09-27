<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete(); $table->timestamps();
        });
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('cart_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete(); $table->unsignedInteger('quantity'); $table->timestamps(); $table->unique(['cart_id','product_variant_id']);
        });
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->constrained()->cascadeOnDelete(); $table->timestamps(); $table->unique(['user_id','product_id']);
        });
        Schema::create('coupons', function (Blueprint $table) {
            $table->id(); $table->string('code',50)->unique(); $table->enum('type',['percent','fixed']); $table->decimal('value',12,2); $table->decimal('minimum_order',12,2)->default(0); $table->decimal('max_discount',12,2)->nullable(); $table->dateTime('start_at')->nullable(); $table->dateTime('end_at')->nullable(); $table->unsignedInteger('usage_limit')->nullable(); $table->unsignedInteger('per_user_limit')->nullable(); $table->unsignedInteger('used_count')->default(0); $table->enum('status',['active','inactive'])->default('active'); $table->timestamps();
        });
        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id(); $table->foreignId('coupon_id')->constrained()->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->decimal('discount_amount',12,2)->default(0); $table->timestamp('used_at')->useCurrent(); $table->index(['coupon_id','user_id']);
        });
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('code',50)->unique(); $table->enum('status',['active','inactive'])->default('active'); $table->timestamps();
        });
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id(); $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete(); $table->string('name')->default('Standard'); $table->decimal('fee',12,2); $table->decimal('free_shipping_threshold',12,2)->nullable(); $table->enum('status',['active','inactive'])->default('active'); $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('shipping_rates'); Schema::dropIfExists('shipping_zones'); Schema::dropIfExists('coupon_usages'); Schema::dropIfExists('coupons'); Schema::dropIfExists('wishlists'); Schema::dropIfExists('cart_items'); Schema::dropIfExists('carts');
    }
};

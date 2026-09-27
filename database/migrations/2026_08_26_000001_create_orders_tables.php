<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_code',32)->nullable()->unique();
            $table->uuid('checkout_token')->unique();
            $table->string('recipient_name'); $table->string('email'); $table->string('phone',30);
            $table->string('province'); $table->string('district'); $table->string('ward'); $table->string('address'); $table->text('note')->nullable();
            $table->string('coupon_code',50)->nullable(); $table->string('shipping_zone_name')->nullable();
            $table->decimal('subtotal',12,2); $table->decimal('discount',12,2)->default(0); $table->decimal('shipping_fee',12,2)->default(0); $table->decimal('total',12,2);
            $table->enum('payment_method',['cod'])->default('cod');
            $table->enum('status',['pending','confirmed','packing','shipping','completed','cancelled'])->default('pending')->index();
            $table->text('cancelled_reason')->nullable(); $table->timestamp('cancelled_at')->nullable(); $table->timestamps();
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name'); $table->string('product_sku',100); $table->string('color_name'); $table->string('size_name',30);
            $table->decimal('price',12,2); $table->unsignedInteger('quantity'); $table->decimal('subtotal',12,2); $table->timestamps();
        });
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('order_id')->constrained()->cascadeOnDelete(); $table->string('from_status')->nullable(); $table->string('to_status'); $table->text('note')->nullable(); $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::table('coupon_usages', function (Blueprint $table) { $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete(); });
    }
    public function down(): void
    {
        Schema::table('coupon_usages',fn(Blueprint $table)=>$table->dropConstrainedForeignId('order_id')); Schema::dropIfExists('order_status_logs'); Schema::dropIfExists('order_items'); Schema::dropIfExists('orders');
    }
};

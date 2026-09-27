<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use App\Services\MomoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Lab06OrderMomoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        $category = Category::create([
            'name' => 'Áo Sơ Mi Nam',
            'slug' => 'ao-so-mi-nam',
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Áo Sơ Mi Oxford',
            'slug' => 'ao-so-mi-oxford',
            'sku' => 'SMI-OXF-001',
            'price' => 200000,
            'sale_price' => null,
            'status' => 'active',
        ]);
    }

    public function test_payment_transactions_table_and_model_relationship()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn A',
            'phone' => '0912345678',
            'address' => '123 Nguyễn Huệ',
            'total_price' => 230000,
            'status' => 'pending',
            'payment_method' => 'momo',
            'to_district_id' => 1442,
            'to_ward_code' => '20308',
            'ghn_total_fee' => 30000,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 200000,
        ]);

        $this->assertInstanceOf(Product::class, $item->product);

        $tx = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'gateway_order_id' => 'ORD_123',
            'amount' => 230000,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'id' => $tx->id,
            'gateway' => 'momo',
            'status' => 'pending',
        ]);

        $this->assertTrue($order->paymentTransactions->contains($tx));
        $this->assertEquals($order->id, $tx->order->id);
    }

    public function test_user_can_access_payment_page_with_session_cart()
    {
        $cart = [
            $this->product->id => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 200000,
                'quantity' => 2,
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['cart' => $cart])
            ->get(route('user.payment.index'));

        $response->assertStatus(200);
        $response->assertSee('Đặt hàng & Thanh toán');
        $response->assertSee('Thanh toán khi nhận hàng (COD)');
        $response->assertSee('Cổng thanh toán MoMo');
    }

    public function test_process_payment_with_cod()
    {
        $cart = [
            $this->product->id => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 200000,
                'quantity' => 1,
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['cart' => $cart])
            ->post(route('user.payment.process'), [
                'name' => 'Nguyễn Văn A',
                'phone' => '0912345678',
                'address' => '123 Lê Lợi',
                'to_district_id' => 1442,
                'to_ward_code' => '20308',
                'payment_method' => 'cod',
            ]);

        $response->assertRedirect(route('user.orders.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn A',
            'phone' => '0912345678',
            'status' => 'cod_ordered',
            'shipping_status' => 'ready_to_pick',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'gateway' => 'cod',
            'status' => 'pending',
        ]);

        // Cart should be cleared
        $this->assertEmpty(session('cart'));
    }

    public function test_process_payment_with_momo_redirects_to_start()
    {
        $cart = [
            $this->product->id => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 200000,
                'quantity' => 1,
            ],
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['cart' => $cart])
            ->post(route('user.payment.process'), [
                'name' => 'Nguyễn Văn B',
                'phone' => '0987654321',
                'address' => '456 Hai Bà Trưng',
                'to_district_id' => 1442,
                'to_ward_code' => '20308',
                'payment_method' => 'momo',
            ]);

        $order = Order::where('user_id', $this->user->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);

        $response->assertRedirect(route('user.orders.momo.start', $order));

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'momo',
            'status' => 'pending',
        ]);
    }

    public function test_momo_start_calls_momo_service_and_redirects()
    {
        Http::fake([
            'https://test-payment.momo.vn/*' => Http::response([
                'resultCode' => 0,
                'message' => 'Success',
                'payUrl' => 'https://test-payment.momo.vn/v2/gateway/pay?token=xyz123',
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn B',
            'phone' => '0987654321',
            'address' => '456 Hai Bà Trưng',
            'total_price' => 230000,
            'status' => 'pending',
            'payment_method' => 'momo',
            'to_district_id' => 1442,
            'to_ward_code' => '20308',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('user.orders.momo.start', $order));

        $response->assertRedirect('https://test-payment.momo.vn/v2/gateway/pay?token=xyz123');

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'momo',
            'status' => 'initiated',
        ]);
    }

    public function test_momo_callback_success_marks_paid_and_creates_ghn()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn C',
            'phone' => '0912345678',
            'address' => '789 Điện Biên Phủ',
            'total_price' => 200000,
            'status' => 'pending',
            'payment_method' => 'momo',
            'to_district_id' => 1442,
            'to_ward_code' => '20308',
        ]);

        $orderIdGateway = $order->id . '_123_' . time();
        $tx = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'gateway_order_id' => $orderIdGateway,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        // Mock MomoService signature verification
        $this->mock(MomoService::class, function ($mock) {
            $mock->shouldReceive('isValidSuccessfulResponse')->andReturn(true);
            $mock->shouldReceive('markPaid')->andReturnNull();
        });

        $payload = [
            'partnerCode' => 'MOMOBKUN20180529',
            'orderId' => $orderIdGateway,
            'amount' => 200000,
            'orderInfo' => 'Thanh toan don hang #' . $order->id,
            'transId' => '1234567890',
            'resultCode' => 0,
            'message' => 'Giao dịch thành công.',
            'extraData' => (string) $order->id,
            'signature' => 'mocked_valid_signature',
        ];

        $response = $this->actingAs($this->user)
            ->get(route('user.payment.momo.callback', $payload));

        $response->assertRedirect(route('user.orders.index'));
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertNotEmpty($order->ghn_order_code);
        $this->assertEquals('ready_to_pick', $order->shipping_status);
    }

    public function test_momo_callback_failure_marks_failed_and_redirects_with_error()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn D',
            'phone' => '0912345678',
            'address' => '789 Điện Biên Phủ',
            'total_price' => 200000,
            'status' => 'pending',
            'payment_method' => 'momo',
            'to_district_id' => 1442,
            'to_ward_code' => '20308',
        ]);

        $orderIdGateway = $order->id . '_456_' . time();
        $tx = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'gateway_order_id' => $orderIdGateway,
            'amount' => 200000,
            'status' => 'pending',
        ]);

        $this->mock(MomoService::class, function ($mock) {
            $mock->shouldReceive('isValidSuccessfulResponse')->andReturn(false);
            $mock->shouldReceive('isValidResponse')->andReturn(true);
            $mock->shouldReceive('markFailed')->andReturnNull();
        });

        $payload = [
            'partnerCode' => 'MOMOBKUN20180529',
            'orderId' => $orderIdGateway,
            'amount' => 200000,
            'resultCode' => 1006, // Người dùng hủy giao dịch
            'message' => 'Giao dịch bị từ chối bởi người dùng.',
            'extraData' => (string) $order->id,
            'signature' => 'valid_sig_but_failure_code',
        ];

        $response = $this->actingAs($this->user)
            ->get(route('user.payment.momo.callback', $payload));

        $response->assertRedirect(route('user.orders.index'));
        $response->assertSessionHas('error');
    }

    public function test_pay_again_creates_new_transaction_without_creating_new_order()
    {
        Http::fake([
            'https://test-payment.momo.vn/*' => Http::response([
                'resultCode' => 0,
                'message' => 'Success',
                'payUrl' => 'https://test-payment.momo.vn/v2/gateway/pay?token=payagain123',
            ], 200),
        ]);

        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn E',
            'phone' => '0912345678',
            'address' => '789 Điện Biên Phủ',
            'total_price' => 200000,
            'status' => 'pending',
            'payment_method' => 'momo',
            'to_district_id' => 1442,
            'to_ward_code' => '20308',
        ]);

        // First failed attempt
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'amount' => 200000,
            'status' => 'failed',
        ]);

        $initialOrderCount = Order::count();
        $this->assertEquals(1, $order->paymentTransactions()->count());

        // Call pay again
        $response = $this->actingAs($this->user)
            ->get(route('user.orders.momo.pay', $order));

        $response->assertRedirect('https://test-payment.momo.vn/v2/gateway/pay?token=payagain123');

        // Order count should remain identical (no new order created)
        $this->assertEquals($initialOrderCount, Order::count());

        // A new transaction should be created (now 2 transactions for this order: 1-N)
        $this->assertEquals(2, $order->paymentTransactions()->count());
    }

    public function test_momo_ipn_webhook_bypasses_csrf()
    {
        $this->mock(MomoService::class, function ($mock) {
            $mock->shouldReceive('isValidSuccessfulResponse')->andReturn(false);
            $mock->shouldReceive('isValidResponse')->andReturn(false);
        });

        // POST to IPN route without CSRF token
        $response = $this->postJson(route('payment.momo.ipn'), [
            'partnerCode' => 'MOMOBKUN20180529',
            'orderId' => '1_1_123',
            'amount' => 10000,
            'resultCode' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Received']);
    }

    public function test_user_orders_index_renders_orders_and_pay_again_button()
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'name' => 'Nguyễn Văn F',
            'phone' => '0912345678',
            'address' => '789 Điện Biên Phủ',
            'total_price' => 200000,
            'status' => 'pending',
            'payment_method' => 'momo',
            'to_district_id' => 1442,
            'to_ward_code' => '20308',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('user.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('Lịch sử đơn hàng');
        $response->assertSee('Thanh toán lại');
        $response->assertSee(route('user.orders.momo.pay', $order));
        $response->assertSee('9704 0000 0000 0018');
    }
}

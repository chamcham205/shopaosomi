<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Lab09FinanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private Order $codOrder;
    private Order $momoOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'email' => 'admin@shirtstore.test',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
            'email' => 'customer@shirtstore.test',
            'name' => 'Nguyễn Văn Khách',
            'phone' => '0988776655',
        ]);

        $this->codOrder = Order::create([
            'user_id' => $this->customer->id,
            'order_code' => 'ORD2026092500000001',
            'checkout_token' => 'token-cod-1',
            'name' => 'Nguyễn Văn Khách',
            'recipient_name' => 'Nguyễn Văn Khách',
            'phone' => '0988776655',
            'province' => 'HCM',
            'district' => '1',
            'ward' => 'Bến Nghé',
            'address' => '123 Lê Lợi',
            'subtotal' => 200000,
            'discount' => 0,
            'shipping_fee' => 30000,
            'total' => 230000,
            'total_price' => 230000,
            'status' => 'cod_ordered',
            'payment_method' => 'cod',
            'shipping_status' => 'ready_to_pick',
        ]);

        PaymentTransaction::create([
            'order_id' => $this->codOrder->id,
            'gateway' => 'cod',
            'amount' => 230000,
            'status' => 'pending',
        ]);

        $this->momoOrder = Order::create([
            'user_id' => $this->customer->id,
            'order_code' => 'ORD2026092500000002',
            'checkout_token' => 'token-momo-2',
            'name' => 'Nguyễn Văn Khách',
            'recipient_name' => 'Nguyễn Văn Khách',
            'phone' => '0988776655',
            'province' => 'HCM',
            'district' => '1',
            'ward' => 'Bến Nghé',
            'address' => '123 Lê Lội',
            'subtotal' => 450000,
            'discount' => 0,
            'shipping_fee' => 30000,
            'total' => 480000,
            'total_price' => 480000,
            'status' => 'paid',
            'payment_method' => 'momo',
            'shipping_status' => 'ready_to_pick',
        ]);

        PaymentTransaction::create([
            'order_id' => $this->momoOrder->id,
            'gateway' => 'momo',
            'amount' => 480000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function test_admin_can_view_finance_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.finance.index'));
        $response->assertStatus(200);
        $response->assertSee('Thống kê tài chính');
        $response->assertSee('Thống kê chỉ số');
        $response->assertSee('Giao dịch thanh toán');
        $response->assertSee('Tổng giá trị đơn hàng');
        $response->assertSee('Chờ thanh toán');
        $response->assertSee('Đã thanh toán');
        $response->assertSee('COD');
        $response->assertSee('MoMo');
    }

    public function test_admin_can_filter_finance_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.finance.index', [
            'search' => 'Khách',
            'gateway' => 'cod',
            'payment_status' => 'pending',
            'min_amount' => 100000,
            'max_amount' => 300000,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Thống kê tài chính');
    }

    public function test_admin_can_view_finance_transactions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.finance.transactions'));
        $response->assertStatus(200);
        $response->assertSee('Giao dịch thanh toán');
        $response->assertSee('Danh sách giao dịch');
        $response->assertSee('ORD2026092500000001');
        $response->assertSee('ORD2026092500000002');
    }

    public function test_admin_can_sort_finance_transactions(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.finance.transactions', [
            'sort' => 'amount_desc',
        ]));
        $response->assertStatus(200);
        $response->assertSee('ORD2026092500000002');
    }

    public function test_admin_can_update_cod_status_to_paid(): void
    {
        $payment = $this->codOrder->paymentTransactions()->first();

        $response = $this->actingAs($this->admin)->patch(route('admin.finance.update-status', $this->codOrder), [
            'payment_status' => 'paid',
            'current_payment_status' => 'pending',
            'current_order_status' => 'cod_ordered',
            'current_payment_id' => $payment->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('cod_paid', $this->codOrder->fresh()->status);
        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_admin_cannot_update_invalid_cod_transition(): void
    {
        $payment = $this->codOrder->paymentTransactions()->first();
        // Cố tình gủi transition không hợp lệ: từ pending sang refunded trực tiếp
        $response = $this->actingAs($this->admin)->patch(route('admin.finance.update-status', $this->codOrder), [
            'payment_status' => 'refunded',
            'current_payment_status' => 'pending',
            'current_order_status' => 'cod_ordered',
            'current_payment_id' => $payment->id,
        ]);

        $response->assertSessionHasErrors('payment_status');
    }

    public function test_admin_cannot_update_non_cod_order(): void
    {
        $payment = $this->momoOrder->paymentTransactions()->first();

        $response = $this->actingAs($this->admin)->patch(route('admin.finance.update-status', $this->momoOrder), [
            'payment_status' => 'paid',
            'current_payment_status' => 'paid',
            'current_order_status' => 'paid',
            'current_payment_id' => $payment->id,
        ]);

        $response->assertSessionHasErrors('payment_status');
    }

    public function test_customer_cannot_access_finance(): void
    {
        $response = $this->actingAs($this->customer)->get(route('admin.finance.index'));
        $response->assertForbidden();

        $responseTx = $this->actingAs($this->customer)->get(route('admin.finance.transactions'));
        $responseTx->assertForbidden();
    }
}

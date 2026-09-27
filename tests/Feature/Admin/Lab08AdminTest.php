<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Lab08AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;

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
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Danh sách người dùng');
        $response->assertSee($this->customer->email);
        $response->assertDontSee('fa-trash');
        $response->assertDontSee('btn-danger');
    }

    public function test_admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.create'));
        $response->assertStatus(200);

        $storeResponse = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Nguyễn Mới',
            'email' => 'nguyenmoi@example.com',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $storeResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'nguyenmoi@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_admin_can_edit_and_update_user(): void
    {
        $targetUser = User::factory()->create(['role' => 'customer', 'name' => 'Cũ']);

        $editResponse = $this->actingAs($this->admin)->get(route('admin.users.edit', $targetUser));
        $editResponse->assertStatus(200);

        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $targetUser), [
            'name' => 'Đã Đổi Tên',
            'email' => $targetUser->email,
            'role' => 'admin',
        ]);

        $updateResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Đã Đổi Tên',
            'role' => 'admin',
        ]);
    }

    public function test_admin_cannot_delete_user(): void
    {
        $targetUser = User::factory()->create(['role' => 'customer']);

        $deleteResponse = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $targetUser));
        $deleteResponse->assertRedirect(route('admin.users.index'));
        $deleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $targetUser->id]);
    }

    public function test_admin_orders_index_with_tabs_and_filters(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_code' => 'DH000099',
            'checkout_token' => 'token-99',
            'recipient_name' => 'Test Khách',
            'phone' => '0912345678',
            'province' => 'HCM',
            'district' => '1',
            'ward' => 'Bến Nghé',
            'address' => '123 Lê Lợi',
            'subtotal' => 300000,
            'discount' => 0,
            'shipping_fee' => 30000,
            'total' => 330000,
            'status' => 'pending',
            'shipping_status' => 'ready_to_pick',
        ]);

        $responseAll = $this->actingAs($this->admin)->get(route('admin.orders.index', ['tab' => 'all']));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('DH000099');
        $responseAll->assertSee('Tất cả');

        $responseReady = $this->actingAs($this->admin)->get(route('admin.orders.index', ['tab' => 'ready']));
        $responseReady->assertStatus(200);
        $responseReady->assertSee('DH000099');

        $responseDelivered = $this->actingAs($this->admin)->get(route('admin.orders.index', ['tab' => 'delivered']));
        $responseDelivered->assertStatus(200);
        $responseDelivered->assertDontSee('DH000099');
    }

    public function test_admin_reports_index_and_charts(): void
    {
        $responseIndex = $this->actingAs($this->admin)->get(route('admin.reports.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Báo cáo doanh thu');
        $responseIndex->assertSee('Bảng số liệu');
        $responseIndex->assertSee('Biểu đồ');

        $responseCharts = $this->actingAs($this->admin)->get(route('admin.reports.charts'));
        $responseCharts->assertStatus(200);
        $responseCharts->assertSee('Biểu đồ báo cáo doanh thu');
        $responseCharts->assertSee('categoryRevenueChart');
        $responseCharts->assertSee('revenueByDateChart');
    }

    public function test_admin_can_create_customer(): void
    {
        $responseCreate = $this->actingAs($this->admin)->get(route('admin.customers.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Thêm khách hàng');

        $responseIndex = $this->actingAs($this->admin)->get(route('admin.customers.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Thêm khách hàng');

        $responseStore = $this->actingAs($this->admin)->post(route('admin.customers.store'), [
            'name' => 'Khách Mới Tạo',
            'email' => 'khachmoi@gmail.com',
            'phone' => '0988776655',
            'password' => 'secret123',
            'status' => 'active',
        ]);

        $responseStore->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'khachmoi@gmail.com',
            'role' => 'customer',
            'phone' => '0988776655',
        ]);
    }

    public function test_admin_can_edit_and_update_customer(): void
    {
        $responseEdit = $this->actingAs($this->admin)->get(route('admin.customers.edit', $this->customer));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Chỉnh sửa khách hàng');
        $responseEdit->assertSee($this->customer->email);

        $responseUpdate = $this->actingAs($this->admin)->put(route('admin.customers.update', $this->customer), [
            'name' => 'Tên Mới Sau Khi Sửa',
            'email' => 'emailmoi@gmail.com',
            'phone' => '0911223344',
            'status' => 'locked',
        ]);

        $responseUpdate->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseHas('users', [
            'id' => $this->customer->id,
            'name' => 'Tên Mới Sau Khi Sửa',
            'email' => 'emailmoi@gmail.com',
            'phone' => '0911223344',
            'status' => 'locked',
        ]);
    }
}

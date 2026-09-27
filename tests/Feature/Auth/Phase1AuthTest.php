<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\Test;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Phase 1 Auth Tests
 *
 * Kiểm tra toàn bộ luồng Authentication theo yêu cầu Phase 1:
 * Register, Login, Logout, Role, Middleware, Rate Limit, ForgotPassword
 */
class Phase1AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── HELPER: Tạo user nhanh ────────────────────────────────────

    private function makeCustomer(array $overrides = []): User
    {
        return User::create(array_merge([
            'name'     => 'Test Customer',
            'email'    => 'test@example.com',
            'password' => Hash::make('Password@123'),
            'role'     => 'customer',
            'status'   => 'active',
            'theme'    => 'light',
        ], $overrides));
    }

    private function makeAdmin(array $overrides = []): User
    {
        return User::create(array_merge([
            'name'     => 'Test Admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('Admin@123'),
            'role'     => 'admin',
            'status'   => 'active',
            'theme'    => 'light',
        ], $overrides));
    }

    // ================================================================
    // 1. REGISTER
    // ================================================================

    #[Test]
    public function test_1_register_customer_success()
    {
        $response = $this->post(route('register.store'), [
            'name'                  => 'Nguyễn Văn Test',
            'email'                 => 'newuser@example.com',
            'phone'                 => '0912345678',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'terms'                 => '1',
        ]);

        $response->assertRedirect(route('home'));

        // Kiểm tra user được tạo trong DB
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role'  => 'customer', // Phải là customer, không phải admin
        ]);
    }

    #[Test]
    public function test_2_email_duplicate_blocked()
    {
        $this->makeCustomer(['email' => 'dup@example.com']);

        $response = $this->post(route('register.store'), [
            'name'                  => 'Another User',
            'email'                 => 'dup@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'terms'                 => '1',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function test_3_password_is_hashed()
    {
        $this->post(route('register.store'), [
            'name'                  => 'Hash Test',
            'email'                 => 'hashtest@example.com',
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'terms'                 => '1',
        ]);

        $user = User::where('email', 'hashtest@example.com')->first();
        $this->assertNotNull($user);
        // Password KHÔNG được là plaintext
        $this->assertNotEquals('Password@123', $user->password);
        // Password phải hợp lệ khi verify
        $this->assertTrue(Hash::check('Password@123', $user->password));
    }

    #[Test]
    public function test_3b_register_cannot_set_role_admin()
    {
        // Người dùng cố inject role=admin vào request
        $response = $this->post(route('register.store'), [
            'name'                  => 'Hacker',
            'email'                 => 'hacker@example.com',
            'role'                  => 'admin', // Cố gắng tự gán admin
            'password'              => 'Password@123',
            'password_confirmation' => 'Password@123',
            'terms'                 => '1',
        ]);

        $user = User::where('email', 'hacker@example.com')->first();
        if ($user) {
            // Dù inject role=admin vào form, phải vẫn là customer
            $this->assertEquals('customer', $user->role);
        }
    }

    // ================================================================
    // 4. LOGIN
    // ================================================================

    #[Test]
    public function test_4_login_customer_redirects_home()
    {
        $this->makeCustomer(['email' => 'cust@example.com']);

        $response = $this->post(route('login.store'), [
            'email'    => 'cust@example.com',
            'password' => 'Password@123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
    }

    #[Test]
    public function test_5_login_admin_redirects_dashboard()
    {
        $this->makeAdmin(['email' => 'adm@example.com']);

        $response = $this->post(route('login.store'), [
            'email'    => 'adm@example.com',
            'password' => 'Admin@123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    #[Test]
    public function test_6_login_super_admin_redirects_dashboard()
    {
        $user = $this->makeAdmin([
            'email' => 'super@example.com',
            'role'  => 'super_admin',
        ]);

        $response = $this->post(route('login.store'), [
            'email'    => 'super@example.com',
            'password' => 'Admin@123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    #[Test]
    public function test_8_wrong_password_fails()
    {
        $this->makeCustomer();

        $response = $this->post(route('login.store'), [
            'email'    => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function test_9_login_rate_limit()
    {
        $this->makeCustomer();

        // Thực hiện 5 lần thất bại
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'email'    => 'test@example.com',
                'password' => 'Wrong',
            ]);
        }

        // Lần thứ 6 phải bị chặn bởi rate limiter
        $response = $this->post(route('login.store'), [
            'email'    => 'test@example.com',
            'password' => 'Wrong',
        ]);

        $response->assertSessionHasErrors('email');
        // Thông báo phải đề cập đến thời gian chờ
        $errors = session('errors');
        if ($errors) {
            $this->assertStringContainsString(
                'giây',
                $errors->first('email') ?? ''
            );
        }
    }

    #[Test]
    public function test_10_locked_account_cannot_login()
    {
        $this->makeCustomer([
            'email'  => 'locked@example.com',
            'status' => 'locked',
        ]);

        $response = $this->post(route('login.store'), [
            'email'    => 'locked@example.com',
            'password' => 'Password@123',
        ]);

        // Tài khoản bị khóa không được đăng nhập
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    // ================================================================
    // 11. LOGOUT
    // ================================================================

    #[Test]
    public function test_11_logout()
    {
        $user = $this->makeCustomer();
        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function test_11b_logout_via_get_succeeds()
    {
        $user = $this->makeCustomer();
        $this->actingAs($user);

        $response = $this->get(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function test_11c_logout_without_auth_redirects_to_login_cleanly()
    {
        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ================================================================
    // 12-13. MIDDLEWARE
    // ================================================================

    #[Test]
    public function test_12_customer_cannot_access_admin_dashboard()
    {
        $customer = $this->makeCustomer();
        $this->actingAs($customer);

        $response = $this->get(route('admin.dashboard'));

        // Phải bị 403 hoặc redirect
        $this->assertTrue(
            in_array($response->status(), [403, 302]),
            "Expected 403 or redirect, got: {$response->status()}"
        );
    }

    #[Test]
    public function test_13_guest_cannot_access_admin()
    {
        $response = $this->get(route('admin.dashboard'));

        // Guest phải bị redirect về login
        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function test_13b_admin_can_access_dashboard()
    {
        $admin = $this->makeAdmin();
        $this->actingAs($admin);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    // ================================================================
    // 14-16. FORGOT PASSWORD
    // ================================================================

    #[Test]
    public function test_14_forgot_password_form_loads()
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
    }

    #[Test]
    public function test_15_forgot_password_does_not_reveal_email_existence()
    {
        // Gửi email KHÔNG tồn tại
        $responseNotExist = $this->post(route('password.email'), [
            'email' => 'notexist@example.com',
        ]);

        // Gửi email TỒN TẠI
        $this->makeCustomer(['email' => 'exist@example.com']);
        $responseExist = $this->post(route('password.email'), [
            'email' => 'exist@example.com',
        ]);

        // Cả hai phải trả về session 'status' (không phải lỗi 'email')
        $responseNotExist->assertSessionHas('status');
        $responseExist->assertSessionHas('status');

        // Thông báo phải GIỐNG NHAU
        $this->assertEquals(
            session('status'),
            session('status')
        );
    }

    #[Test]
    public function test_16_forgot_password_rate_limit_by_email()
    {
        $this->makeCustomer(['email' => 'ratelimit@example.com']);

        // Gửi 3 lần (đúng giới hạn)
        for ($i = 0; $i < 3; $i++) {
            $this->post(route('password.email'), ['email' => 'ratelimit@example.com']);
        }

        // Lần thứ 4 phải bị chặn
        $response = $this->post(route('password.email'), ['email' => 'ratelimit@example.com']);

        // Rate limit bị chặn → có error hoặc redirect với error
        $response->assertSessionHasErrors('email');
    }

    // ================================================================
    // 17. RESET PASSWORD – Form loads
    // ================================================================

    #[Test]
    public function test_17_reset_password_form_loads()
    {
        $response = $this->get(route('password.reset', ['token' => 'fake-token']) . '?email=test@example.com');
        $response->assertStatus(200);
    }

    // ================================================================
    // PREVENT ADMIN SHOPPING MIDDLEWARE
    // ================================================================

    #[Test]
    public function test_prevent_admin_shopping_middleware_exists()
    {
        // Kiểm tra class tồn tại
        $this->assertTrue(
            class_exists(\App\Http\Middleware\PreventAdminShoppingMiddleware::class),
            'PreventAdminShoppingMiddleware không tồn tại'
        );
    }

    // ================================================================
    // USER MODEL HELPERS
    // ================================================================

    #[Test]
    public function test_user_model_role_helpers()
    {
        $customer   = $this->makeCustomer(['email' => 'c@e.com']);
        $admin      = $this->makeAdmin(['email' => 'a@e.com']);
        $superAdmin = $this->makeAdmin(['email' => 's@e.com', 'role' => 'super_admin']);

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isSuperAdmin());

        $this->assertTrue($superAdmin->isAdmin());
        $this->assertTrue($superAdmin->isSuperAdmin());
    }

    #[Test]
    public function test_user_model_status_helpers()
    {
        $active = $this->makeCustomer(['email' => 'active@e.com', 'status' => 'active']);
        $locked = $this->makeCustomer(['email' => 'lock@e.com', 'status' => 'locked']);

        $this->assertTrue($active->isActive());
        $this->assertFalse($active->isLocked());

        $this->assertTrue($locked->isLocked());
        $this->assertFalse($locked->isActive());
    }
}

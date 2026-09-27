<?php

namespace Tests\Feature\Customer;

use App\Mail\OrderCancelledMail;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderShippingMail;
use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\Size;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class Phase8EmailTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    private function user(string $role = 'customer', ?string $email = null): User
    {
        return User::factory()->create(['role' => $role, 'status' => 'active', 'email' => $email ?? fake()->unique()->safeEmail()]);
    }

    private function variant(int $stock = 5, float $price = 500000): ProductVariant
    {
        $this->sequence++;
        $category = Category::create(['name' => 'Cat '.$this->sequence, 'slug' => 'cat-'.$this->sequence, 'status' => 'active']);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Áo sơ mi '.$this->sequence, 'slug' => 'ao-'.$this->sequence, 'sku' => 'P-'.$this->sequence, 'price' => $price, 'status' => 'active']);
        $color = Color::create(['name' => 'Xanh', 'hex_code' => '#112233', 'status' => 'active']);
        $size = Size::create(['name' => 'M'.$this->sequence, 'status' => 'active']);

        return $product->variants()->create(['color_id' => $color->id, 'size_id' => $size->id, 'sku' => 'V-'.$this->sequence, 'stock' => $stock]);
    }

    private function zone(): ShippingZone
    {
        $this->sequence++;
        $zone = ShippingZone::create(['name' => 'Zone '.$this->sequence, 'code' => 'Z'.$this->sequence, 'status' => 'active']);
        $zone->rates()->create(['name' => 'Standard', 'fee' => 30000, 'status' => 'active']);
        return $zone;
    }

    private function place(User $user, ?ProductVariant $variant = null, string $email = 'buyer@example.com'): Order
    {
        $variant ??= $this->variant();
        app(CartService::class)->add($user, $variant, 1);
        return app(OrderService::class)->placeOrder($user, [
            'recipient_name' => 'Nguyễn Văn A', 'email' => $email, 'phone' => '0900000000',
            'province' => 'HCM', 'district' => 'Quận 1', 'ward' => 'Bến Nghé',
            'address' => '1 Nguyễn Huệ', 'note' => null,
        ], fake()->uuid(), null, $this->zone()->id);
    }

    private function order(string $status = 'pending', string $email = 'buyer@example.com'): Order
    {
        $this->sequence++;
        $user = $this->user();
        return Order::create([
            'user_id' => $user->id, 'order_code' => 'ORD20260828'.str_pad((string) $this->sequence, 8, '0', STR_PAD_LEFT),
            'checkout_token' => fake()->uuid(), 'recipient_name' => $user->name, 'email' => $email,
            'phone' => '0900000000', 'province' => 'HCM', 'district' => '1', 'ward' => 'A', 'address' => 'Street',
            'subtotal' => 500000, 'discount' => 0, 'shipping_fee' => 30000, 'total' => 530000, 'payment_method' => 'cod', 'status' => $status,
        ]);
    }

    public function test_order_success_triggers_placed_email(): void { Mail::fake(); $this->place($this->user()); Mail::assertSent(OrderPlacedMail::class); }
    public function test_failed_order_does_not_send_email(): void { Mail::fake(); $user=$this->user(); $variant=$this->variant(1); app(CartService::class)->add($user,$variant,1); $variant->update(['stock'=>0]); try { app(OrderService::class)->placeOrder($user,['recipient_name'=>'A','email'=>'a@example.com','phone'=>'1','province'=>'P','district'=>'D','ward'=>'W','address'=>'A'],fake()->uuid(),null,$this->zone()->id); } catch (ValidationException) {} Mail::assertNothingSent(); }
    public function test_confirmed_transition_triggers_email(): void { Mail::fake(); app(OrderService::class)->transitionByAdmin($this->user('admin'),$this->order(),'confirmed'); Mail::assertSent(OrderConfirmedMail::class); }
    public function test_shipping_transition_triggers_email(): void { Mail::fake(); app(OrderService::class)->transitionByAdmin($this->user('admin'),$this->order('packing'),'shipping'); Mail::assertSent(OrderShippingMail::class); }
    public function test_completed_transition_triggers_email(): void { Mail::fake(); app(OrderService::class)->transitionByAdmin($this->user('admin'),$this->order('shipping'),'completed'); Mail::assertSent(OrderCompletedMail::class); }
    public function test_customer_cancel_triggers_email(): void { Mail::fake(); $user=$this->user(); $order=$this->order(); $order->update(['user_id'=>$user->id]); app(OrderService::class)->cancelOrder($user,$order,'Đổi ý'); Mail::assertSent(OrderCancelledMail::class); }
    public function test_admin_cancel_triggers_email(): void { Mail::fake(); app(OrderService::class)->cancelByAdmin($this->user('admin'),$this->order(),'Hết hàng'); Mail::assertSent(OrderCancelledMail::class); }
    public function test_invalid_transition_sends_no_email(): void { Mail::fake(); try { app(OrderService::class)->transitionByAdmin($this->user('admin'),$this->order(),'completed'); } catch (ValidationException) {} Mail::assertNothingSent(); }
    public function test_email_failure_does_not_rollback_order(): void { Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('mail unavailable')); $order=$this->place($this->user()); $this->assertDatabaseHas('orders',['id'=>$order->id,'status'=>'pending']); }
    public function test_order_email_contains_correct_order_code(): void { Mail::fake(); $order=$this->place($this->user()); Mail::assertSent(OrderPlacedMail::class,fn($mail)=>$mail->order->order_code===$order->order_code && str_contains($mail->render(),$order->order_code)); }
    public function test_order_email_uses_snapshot_prices_and_items(): void { Mail::fake(); $variant=$this->variant(5,500000); $order=$this->place($this->user(),$variant); $variant->product->update(['name'=>'Tên đã đổi','price'=>900000]); Mail::assertSent(OrderPlacedMail::class,fn($mail)=>str_contains($mail->render(),'Áo sơ mi') && str_contains($mail->render(),'500.000')); $this->assertEquals(500000,$order->items->first()->price); }
    public function test_shipping_email_sent_to_correct_customer(): void { Mail::fake(); $order=$this->order('packing','right@example.com'); app(OrderService::class)->transitionByAdmin($this->user('admin'),$order,'shipping'); Mail::assertSent(OrderShippingMail::class,fn($mail)=>$mail->hasTo('right@example.com')); }
    public function test_customer_a_never_receives_customer_b_order_email(): void { Mail::fake(); $this->place($this->user(email:'a@example.com'),null,'b@example.com'); Mail::assertSent(OrderPlacedMail::class,fn($mail)=>$mail->hasTo('b@example.com') && !$mail->hasTo('a@example.com')); }
    public function test_forgot_password_notification_sent_for_valid_account(): void { Notification::fake(); $user=$this->user(email:'reset-valid@example.com'); $this->withServerVariables(['REMOTE_ADDR'=>'10.8.0.1'])->post(route('password.email'),['email'=>$user->email]); Notification::assertSentTo($user,ResetPasswordNotification::class); }
    public function test_reset_token_expiry_remains_fifteen_minutes(): void { $this->assertSame(15,(int)config('auth.passwords.users.expire')); }
    public function test_forgot_password_enumeration_protection_remains_intact(): void { Notification::fake(); $user=$this->user(email:'known@example.com'); $a=$this->withServerVariables(['REMOTE_ADDR'=>'10.8.0.2'])->post(route('password.email'),['email'=>$user->email]); $b=$this->withServerVariables(['REMOTE_ADDR'=>'10.8.0.3'])->post(route('password.email'),['email'=>'unknown@example.com']); $this->assertSame($a->getSession()->get('status'),$b->getSession()->get('status')); }
    public function test_existing_forgot_password_rate_limit_remains_active(): void { Notification::fake(); $email='limited-phase8@example.com'; $this->user(email:$email); $key='forgot-password:email:'.$email; RateLimiter::clear($key); for($i=0;$i<3;$i++) $this->withServerVariables(['REMOTE_ADDR'=>'10.8.0.4'])->post(route('password.email'),['email'=>$email]); $this->withServerVariables(['REMOTE_ADDR'=>'10.8.0.4'])->post(route('password.email'),['email'=>$email])->assertSessionHasErrors('email'); RateLimiter::clear($key); }
}

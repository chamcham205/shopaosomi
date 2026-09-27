<?php
namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Models\Size;
use App\Models\User;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\ShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class Phase4CommerceTest extends TestCase
{
    use RefreshDatabase;
    private int $n=0;
    private function user(string $role='customer'):User{return User::factory()->create(['role'=>$role,'status'=>'active']);}
    private function product(float $price=500000):Product{$this->n++;$c=Category::create(['name'=>'Cat'.$this->n,'slug'=>'cat-'.$this->n,'status'=>'active']);return Product::create(['category_id'=>$c->id,'name'=>'Shirt '.$this->n,'slug'=>'shirt-'.$this->n,'sku'=>'SKU-'.$this->n,'price'=>$price,'status'=>'active']);}
    private function variant(Product $p,int $stock=5):ProductVariant{$this->n++;$c=Color::create(['name'=>'Color'.$this->n,'hex_code'=>'#112233','status'=>'active']);$s=Size::create(['name'=>'S'.$this->n,'status'=>'active']);return $p->variants()->create(['color_id'=>$c->id,'size_id'=>$s->id,'sku'=>'VAR-'.$this->n,'stock'=>$stock]);}
    private function coupon(array $a=[]):Coupon{return Coupon::create(array_merge(['code'=>'SAVE10','type'=>'percent','value'=>10,'minimum_order'=>0,'status'=>'active'], $a));}

    public function test_add_cart():void{$u=$this->user();$v=$this->variant($this->product());$this->actingAs($u)->post('/cart',['variant_id'=>$v->id,'quantity'=>2])->assertSessionHasNoErrors();$this->assertDatabaseHas('cart_items',['product_variant_id'=>$v->id,'quantity'=>2]);}
    public function test_different_variants_are_different_lines():void{$u=$this->user();$p=$this->product();$a=$this->variant($p);$b=$this->variant($p);$this->actingAs($u)->post('/cart',['variant_id'=>$a->id,'quantity'=>1]);$this->post('/cart',['variant_id'=>$b->id,'quantity'=>1]);$this->assertEquals(2,$u->cart->items()->count());}
    public function test_quantity_cannot_exceed_stock():void{$u=$this->user();$v=$this->variant($this->product(),2);$this->actingAs($u)->post('/cart',['variant_id'=>$v->id,'quantity'=>3])->assertSessionHasErrors('quantity');$this->assertDatabaseCount('cart_items',0);}
    public function test_add_cart_does_not_reduce_stock():void{$u=$this->user();$v=$this->variant($this->product(),4);$this->actingAs($u)->post('/cart',['variant_id'=>$v->id,'quantity'=>2]);$this->assertEquals(4,$v->fresh()->stock);}
    public function test_update_and_remove_cart():void{$u=$this->user();$v=$this->variant($this->product(),5);$this->actingAs($u)->post('/cart',['variant_id'=>$v->id,'quantity'=>1]);$item=$u->cart->items()->first();$this->patch('/cart/'.$item->id,['quantity'=>3]);$this->assertEquals(3,$item->fresh()->quantity);$this->delete('/cart/'.$item->id);$this->assertDatabaseMissing('cart_items',['id'=>$item->id]);}
    public function test_admin_is_blocked_from_cart():void{$this->actingAs($this->user('admin'))->get('/cart')->assertRedirect(route('admin.dashboard'));}
    public function test_wishlist_add_and_remove():void{$u=$this->user();$p=$this->product();$this->actingAs($u)->post('/wishlist/'.$p->id);$this->assertDatabaseHas('wishlists',['user_id'=>$u->id,'product_id'=>$p->id]);$this->delete('/wishlist/'.$p->id);$this->assertDatabaseMissing('wishlists',['user_id'=>$u->id,'product_id'=>$p->id]);}
    public function test_wishlist_does_not_duplicate():void{$u=$this->user();$p=$this->product();$this->actingAs($u)->post('/wishlist/'.$p->id);$this->post('/wishlist/'.$p->id);$this->assertDatabaseCount('wishlists',1);}
    public function test_percent_coupon():void{$u=$this->user();$c=$this->coupon();$r=app(CouponService::class)->apply($c->code,100000,$u);$this->assertEquals(10000,$r['discount']);}
    public function test_fixed_coupon():void{$u=$this->user();$c=$this->coupon(['code'=>'FIXED','type'=>'fixed','value'=>25000]);$this->assertEquals(25000,app(CouponService::class)->apply('fixed',100000,$u)['discount']);}
    public function test_expired_coupon():void{$this->expectException(ValidationException::class);app(CouponService::class)->apply($this->coupon(['end_at'=>now()->subMinute()])->code,100000,$this->user());}
    public function test_minimum_order():void{$this->expectException(ValidationException::class);app(CouponService::class)->apply($this->coupon(['minimum_order'=>200000])->code,100000,$this->user());}
    public function test_max_discount():void{$u=$this->user();$c=$this->coupon(['value'=>50,'max_discount'=>20000]);$this->assertEquals(20000,app(CouponService::class)->apply($c->code,100000,$u)['discount']);}
    public function test_usage_limit():void{$this->expectException(ValidationException::class);$c=$this->coupon(['usage_limit'=>1,'used_count'=>1]);app(CouponService::class)->apply($c->code,100000,$this->user());}
    public function test_per_user_limit():void{$this->expectException(ValidationException::class);$u=$this->user();$c=$this->coupon(['per_user_limit'=>1]);CouponUsage::create(['coupon_id'=>$c->id,'user_id'=>$u->id]);app(CouponService::class)->apply($c->code,100000,$u);}
    public function test_shipping_rate_and_freeship_threshold():void{$z=ShippingZone::create(['name'=>'HCM','code'=>'HCM','status'=>'active']);$z->rates()->create(['name'=>'Standard','fee'=>35000,'free_shipping_threshold'=>500000,'status'=>'active']);$s=app(ShippingService::class);$this->assertEquals(35000,$s->calculate($z->id,300000));$this->assertEquals(0,$s->calculate($z->id,500000));}
    public function test_server_recalculates_totals_from_current_price():void{$u=$this->user();$p=$this->product(500000);$v=$this->variant($p,5);app(CartService::class)->add($u,$v,2);$this->assertEquals(1000000,app(CartService::class)->summary($u)['subtotal']);$p->update(['sale_price'=>400000,'sale_start'=>now()->subMinute(),'sale_end'=>now()->addHour()]);$this->assertEquals(800000,app(CartService::class)->summary($u)['subtotal']);}
}

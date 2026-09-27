<?php

namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use App\Services\AdminMetricsService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase9SettingsBannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void { parent::setUp(); Cache::forget('site_settings'); }
    private function user(string $role='admin'):User{return User::factory()->create(['role'=>$role,'status'=>'active']);}
    private function settings(array $overrides=[]):array{return array_merge(['site_name'=>'SHIRT STORE','site_description'=>'Store','contact_email'=>'store@example.com','contact_phone'=>'0900000000','address'=>'HCM','low_stock_threshold'=>5,'default_theme'=>'system','announcement'=>''], $overrides);}
    private function banner(array $overrides=[]):Banner{return Banner::create(array_merge(['title'=>'Banner','subtitle'=>'Nội dung','button_text'=>'Mua ngay','target_url'=>'/products','sort_order'=>0,'status'=>'active'], $overrides));}
    private function image():UploadedFile{return UploadedFile::fake()->createWithContent('banner.png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));}
    private function variant(int $stock):ProductVariant{$n=Product::count()+1;$c=Category::create(['name'=>'C'.$n,'slug'=>'c-'.$n,'status'=>'active']);$p=Product::create(['category_id'=>$c->id,'name'=>'P'.$n,'slug'=>'p-'.$n,'sku'=>'P'.$n,'price'=>100000,'status'=>'active']);$color=Color::create(['name'=>'Color'.$n,'hex_code'=>'#112233','status'=>'active']);$size=Size::create(['name'=>'Size'.$n,'status'=>'active']);return $p->variants()->create(['color_id'=>$color->id,'size_id'=>$size->id,'sku'=>'V'.$n,'stock'=>$stock]);}

    public function test_admin_settings_page_200():void{$this->actingAs($this->user())->get('/admin/settings')->assertOk()->assertSee('Cài đặt hệ thống');}
    public function test_customer_blocked_admin_settings():void{$this->actingAs($this->user('customer'))->get('/admin/settings')->assertForbidden();}
    public function test_admin_can_update_general_settings():void{$this->actingAs($this->user())->put('/admin/settings',$this->settings(['site_name'=>'Áo Việt']))->assertSessionHasNoErrors();$this->assertSame('Áo Việt',app(SettingService::class)->get('site_name'));}
    public function test_invalid_settings_rejected():void{$this->actingAs($this->user())->put('/admin/settings',$this->settings(['contact_email'=>'bad','low_stock_threshold'=>-1,'default_theme'=>'blue']))->assertSessionHasErrors(['contact_email','low_stock_threshold','default_theme']);}
    public function test_low_stock_threshold_uses_setting():void{app(SettingService::class)->put($this->settings(['low_stock_threshold'=>2]));$this->assertTrue($this->variant(2)->is_low_stock);$this->assertFalse($this->variant(3)->is_low_stock);}
    public function test_low_stock_threshold_fallback_is_five():void{Cache::forget('site_settings');$this->assertSame(5,app(SettingService::class)->lowStockThreshold());}
    public function test_admin_banners_page_200():void{$this->actingAs($this->user())->get('/admin/banners')->assertOk()->assertSee('Homepage Banners');}
    public function test_admin_can_create_banner():void{Storage::fake('public');$data=['title'=>'Summer','subtitle'=>'Sale','image'=>$this->image(),'button_text'=>'Shop','target_url'=>'/products','sort_order'=>1,'status'=>'active','starts_at'=>null,'ends_at'=>null];$this->actingAs($this->user())->post('/admin/banners',$data)->assertSessionHasNoErrors();$this->assertDatabaseHas('banners',['title'=>'Summer']);}
    public function test_invalid_banner_image_rejected():void{Storage::fake('public');$data=['title'=>'Bad','image'=>UploadedFile::fake()->create('bad.txt',10,'text/plain'),'sort_order'=>0,'status'=>'active'];$this->actingAs($this->user())->post('/admin/banners',$data)->assertSessionHasErrors('image');}
    public function test_inactive_banner_not_shown():void{$this->banner(['title'=>'Hidden banner','status'=>'inactive']);$this->get('/')->assertDontSee('Hidden banner');}
    public function test_active_banner_shown():void{$this->banner(['title'=>'Visible banner']);$this->get('/')->assertOk()->assertSee('Visible banner');}
    public function test_expired_banner_not_shown():void{$this->banner(['title'=>'Expired banner','ends_at'=>now()->subMinute()]);$this->get('/')->assertDontSee('Expired banner');}
    public function test_banner_sort_order_works():void{$this->banner(['title'=>'Second banner','sort_order'=>20]);$this->banner(['title'=>'First banner','sort_order'=>10]);$this->get('/')->assertSeeInOrder(['First banner','Second banner']);}
    public function test_banner_image_storage_path_valid():void{Storage::fake('public');$this->actingAs($this->user())->post('/admin/banners',['title'=>'Stored','image'=>$this->image(),'sort_order'=>0,'status'=>'active']);$banner=Banner::firstOrFail();$this->assertStringStartsWith('banners/',$banner->image_path);Storage::disk('public')->assertExists($banner->image_path);}
    public function test_replacing_banner_image_removes_old_file_safely():void{Storage::fake('public');Storage::disk('public')->put('banners/old.png','old');$banner=$this->banner(['image_path'=>'banners/old.png']);$this->actingAs($this->user())->put('/admin/banners/'.$banner->id,['title'=>'Updated','image'=>$this->image(),'sort_order'=>0,'status'=>'active'])->assertSessionHasNoErrors();Storage::disk('public')->assertMissing('banners/old.png');Storage::disk('public')->assertExists($banner->fresh()->image_path);}
    public function test_customer_home_still_200():void{$this->get('/')->assertOk();}
    public function test_footer_uses_configured_store_data():void{app(SettingService::class)->put($this->settings(['site_name'=>'Store Nine','contact_phone'=>'0123456789','contact_email'=>'hello@nine.test','address'=>'Nine Street']));$this->get('/')->assertSee('Store Nine')->assertSee('0123456789')->assertSee('hello@nine.test')->assertSee('Nine Street');}
    public function test_dashboard_and_report_use_configured_low_stock():void{app(SettingService::class)->put($this->settings(['low_stock_threshold'=>2]));$this->variant(2);$this->variant(3);$this->assertSame(1,app(AdminMetricsService::class)->overview()['lowStockCount']);$admin=$this->user();$this->actingAs($admin)->get('/admin/dashboard')->assertOk();$this->actingAs($admin)->get('/admin/reports')->assertOk();}
    public function test_theme_controls_render():void{$this->get('/')->assertSee('data-theme="light"',false)->assertSee('data-theme="dark"',false)->assertSee('data-theme="system"',false)->assertSee('localStorage',false);}
}

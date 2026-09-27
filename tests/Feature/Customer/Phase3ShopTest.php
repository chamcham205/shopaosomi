<?php
namespace Tests\Feature\Customer;

use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3ShopTest extends TestCase
{
    use RefreshDatabase;
    private int $sequence = 0;

    private function category(string $name = 'Oxford'): Category
    {
        return Category::create(['name'=>$name,'slug'=>str($name)->slug().'-'.$this->sequence++,'status'=>'active']);
    }

    private function product(array $attributes = []): Product
    {
        $this->sequence++;
        return Product::create(array_merge(['category_id'=>$this->category()->id,'name'=>'Oxford Shirt '.$this->sequence,'slug'=>'oxford-shirt-'.$this->sequence,'sku'=>'P3-'.$this->sequence,'brand'=>'Atelier','material'=>'Cotton','fit'=>'Regular','price'=>500000,'status'=>'active'], $attributes));
    }

    private function variant(Product $product, int $stock = 5): array
    {
        $color=Color::create(['name'=>'Navy '.$this->sequence,'hex_code'=>'#112244','status'=>'active']);
        $size=Size::create(['name'=>'M'.$this->sequence,'sort_order'=>1,'status'=>'active']);
        $product->variants()->create(['color_id'=>$color->id,'size_id'=>$size->id,'sku'=>$product->sku.'-V','stock'=>$stock]);
        return [$color,$size];
    }

    public function test_home_returns_200(): void { $this->get('/')->assertOk()->assertSee('Sản phẩm mới'); }
    public function test_products_returns_200(): void { $this->get('/products')->assertOk()->assertSee('Cửa hàng'); }

    public function test_detail_returns_200(): void
    {
        $product=$this->product(); $this->variant($product);
        $this->get('/products/'.$product->slug)->assertOk()->assertSee($product->sku);
    }

    public function test_searches_name_sku_brand_and_material(): void
    {
        $product=$this->product(['name'=>'Needlepoint','sku'=>'FIND-42','brand'=>'Northstar','material'=>'Linen']);
        foreach (['Needlepoint','FIND-42','Northstar','Linen'] as $term) $this->get('/products?q='.urlencode($term))->assertSee($product->name);
    }

    public function test_filters_category(): void
    {
        $category=$this->category('Linen'); $shown=$this->product(['category_id'=>$category->id,'name'=>'Shown Category']); $hidden=$this->product(['name'=>'Hidden Category']);
        $this->get('/products?category='.$category->slug)->assertSee($shown->name)->assertDontSee($hidden->name);
    }

    public function test_filters_color(): void
    {
        $shown=$this->product(['name'=>'Shown Color']); [$color]=$this->variant($shown); $hidden=$this->product(['name'=>'Hidden Color']); $this->variant($hidden);
        $this->get('/products?color='.$color->id)->assertSee($shown->name)->assertDontSee($hidden->name);
    }

    public function test_filters_size(): void
    {
        $shown=$this->product(['name'=>'Shown Size']); [,$size]=$this->variant($shown); $hidden=$this->product(['name'=>'Hidden Size']); $this->variant($hidden);
        $this->get('/products?size='.$size->id)->assertSee($shown->name)->assertDontSee($hidden->name);
    }

    public function test_sorts_by_price(): void
    {
        $expensive=$this->product(['name'=>'Expensive Shirt','price'=>900000]); $cheap=$this->product(['name'=>'Cheap Shirt','price'=>200000]);
        $this->get('/products?sort=price_asc')->assertSeeInOrder([$cheap->name,$expensive->name]);
        $this->get('/products?sort=price_desc')->assertSeeInOrder([$expensive->name,$cheap->name]);
    }

    public function test_hidden_and_deleted_products_are_excluded(): void
    {
        $active=$this->product(['name'=>'Visible Shirt']); $inactive=$this->product(['name'=>'Inactive Shirt','status'=>'inactive']); $deleted=$this->product(['name'=>'Deleted Shirt']); $deleted->delete();
        $this->get('/products')->assertSee($active->name)->assertDontSee($inactive->name)->assertDontSee($deleted->name);
        $this->get('/products/'.$inactive->slug)->assertNotFound(); $this->get('/products/'.$deleted->slug)->assertNotFound();
    }

    public function test_active_sale_price_is_rendered(): void
    {
        $product=$this->product(['price'=>600000,'sale_price'=>450000,'sale_start'=>now()->subHour(),'sale_end'=>now()->addHour()]);
        $this->get('/products/'.$product->slug)->assertOk()->assertSee('450.000')->assertSee('-25%');
    }

    public function test_out_of_stock_product_is_marked_and_variant_stock_is_exposed(): void
    {
        $product=$this->product(); $this->variant($product,0);
        $this->get('/products')->assertSee('Hết hàng');
        $this->get('/products/'.$product->slug)->assertOk()->assertSee('"stock":0',false);
    }
}

<?php
namespace App\Services;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function cart(User $user): Cart { return Cart::firstOrCreate(['user_id'=>$user->id]); }
    public function add(User $user, ProductVariant $variant, int $quantity): CartItem
    {
        $cart=$this->cart($user); $item=$cart->items()->firstOrNew(['product_variant_id'=>$variant->id]); $new=$item->exists?$item->quantity+$quantity:$quantity;
        if ($quantity<1 || $new>$variant->stock) throw ValidationException::withMessages(['quantity'=>'Số lượng vượt quá tồn kho.']);
        $item->quantity=$new; $item->save(); return $item;
    }
    public function update(User $user, CartItem $item, int $quantity): void
    {
        abort_unless($item->cart->user_id===$user->id,404); $item->load('variant');
        if ($quantity<1 || $quantity>$item->variant->stock) throw ValidationException::withMessages(['quantity'=>'Số lượng vượt quá tồn kho.']);
        $item->update(['quantity'=>$quantity]);
    }
    public function summary(User $user, float $discount=0, float $shipping=0): array
    {
        $items=$this->cart($user)->items()->with(['variant.product.images','variant.color','variant.size'])->get();
        $subtotal=$items->sum(fn($item)=>$item->variant->product->current_price*$item->quantity); $discount=min($discount,$subtotal);
        return compact('items','subtotal','discount','shipping')+['total'=>max(0,$subtotal-$discount)+$shipping];
    }
}

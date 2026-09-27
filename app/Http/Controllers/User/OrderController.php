<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Services\CouponService;
use App\Services\GHNOrderService;
use App\Services\GHNService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request, GHNService $ghn, CouponService $coupons)
    {
        $user = $request->user();
        $selectedItems = $request->input('selected_items');

        // Nếu người dùng gửi danh sách các sản phẩm được chọn từ giỏ hàng
        if ($request->filled('selected_items')) {
            $selectedItemIds = is_array($selectedItems) ? $selectedItems : explode(',', (string) $selectedItems);
            $selectedItemIds = array_filter(array_map('intval', $selectedItemIds));

            if ($user && $user->cart && $user->cart->items()->count() > 0) {
                $this->syncDbCartToSession($user, $selectedItemIds);
            }
        } elseif (empty(session('cart', [])) && $user && $user->cart && $user->cart->items()->count() > 0) {
            // Nếu session cart trống, nạp toàn bộ từ DB cart của người dùng
            $this->syncDbCartToSession($user);
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
        }

        $subtotal = (float) collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $totalWeight = collect($cart)->sum(fn($item) => $ghn->productWeight() * (int) $item['quantity']);

        // Xử lý mã giảm giá nếu có trong session
        $discount = 0;
        $coupon = null;
        if ($user && ($code = session('cart_coupon'))) {
            try {
                $res = $coupons->apply($code, $subtotal, $user);
                $discount = (float) ($res['discount'] ?? 0);
                $coupon = $res['coupon'] ?? null;
            } catch (\Throwable) {
                $request->session()->forget('cart_coupon');
            }
        }

        // Danh sách toàn quốc 63 Tỉnh/Thành từ GHN
        $provinces = $ghn->getProvinces();

        // Phí ship ước tính chuẩn
        $estimatedShipping = 30000;

        return view('user.payment.index', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'coupon' => $coupon,
            'totalWeight' => $totalWeight,
            'estimatedShipping' => $estimatedShipping,
            'provinces' => $provinces,
            'user' => $user,
        ]);
    }

    public function processPayment(Request $request, GHNService $ghn, GHNOrderService $ghnOrders, CouponService $coupons)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'email' => 'nullable|email|max:150',
            'address' => 'required|string|max:255',
            'province_name' => 'nullable|string|max:100',
            'district_name' => 'nullable|string|max:100',
            'ward_name' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
            'to_district_id' => 'required|integer',
            'to_ward_code' => 'required|string',
            'payment_method' => 'required|in:cod,momo',
        ]);

        $cart = session('cart', []);
        $user = $request->user();

        if (empty($cart) && $user && $user->cart && $user->cart->items()->count() > 0) {
            $this->syncDbCartToSession($user);
            $cart = session('cart', []);
        }

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Không thể thanh toán vì giỏ hàng trống.');
        }

        // 1. Tính tổng tiền hàng và tổng khối lượng sản phẩm
        $subtotal = (float) collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $totalWeight = collect($cart)->sum(
            fn($item) => $ghn->productWeight() * (int) $item['quantity']
        );

        // 2. Tính lại mã giảm giá nếu có
        $discount = 0;
        $coupon = null;
        if ($user && ($code = session('cart_coupon'))) {
            try {
                $res = $coupons->apply($code, $subtotal, $user);
                $discount = (float) ($res['discount'] ?? 0);
                $coupon = $res['coupon'] ?? null;
            } catch (\Throwable) {
                $discount = 0;
            }
        }

        // 3. Tính lại phí ship chuẩn xác từ GHN trên server
        $feeResponse = $ghn->calculateFee(array_merge([
            'from_district_id' => (int) config('services.ghn.from_district_id', 1442),
            'to_district_id' => (int) $request->to_district_id,
            'to_ward_code' => (string) $request->to_ward_code,
        ], $ghn->packageParameters($totalWeight)));

        $shippingFee = (isset($feeResponse['code']) && $feeResponse['code'] == 200)
            ? (int) ($feeResponse['data']['total'] ?? 30000)
            : 30000;

        // Tổng thanh toán = Tiền hàng - Giảm giá + Phí ship
        $finalTotal = max(0, $subtotal - $discount) + $shippingFee;

        // 4. Tạo đơn hàng và chi tiết đơn hàng trong Database
        $order = DB::transaction(function () use ($request, $user, $shippingFee, $subtotal, $discount, $coupon, $finalTotal, $cart) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_code' => 'ORD' . date('Ymd') . strtoupper(Str::random(6)),
                'name' => $request->name,
                'recipient_name' => $request->name,
                'email' => $request->email ?: ($user?->email ?: 'customer@shirtstore.local'),
                'address' => $request->address,
                'phone' => $request->phone,
                'province' => $request->province_name ?: 'TP. Hồ Chí Minh',
                'district' => $request->district_name ?: 'Quận 1',
                'ward' => $request->ward_name ?: 'Phường Bến Nghé',
                'note' => $request->note,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'discount' => $discount,
                'total_price' => $finalTotal,
                'total' => $finalTotal,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'to_district_id' => (int) $request->to_district_id,
                'to_ward_code' => (string) $request->to_ward_code,
                'ghn_total_fee' => $shippingFee,
                'shipping_status' => 'pending',
            ]);

            foreach ($cart as $item) {
                $productId = $item['id'] ?? $item['product_id'] ?? null;
                $variantId = $item['variant_id'] ?? $item['product_variant_id'] ?? null;

                $displayName = $item['name'] ?? 'Sản phẩm';
                if (!empty($item['variant_label']) && !str_contains($displayName, '(')) {
                    $displayName .= ' (' . $item['variant_label'] . ')';
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'product_name' => $displayName,
                    'product_sku' => $item['sku'] ?? 'SKU-DEFAULT',
                    'color_name' => $item['color'] ?? '',
                    'size_name' => $item['size'] ?? '',
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            if ($coupon && $user) {
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'discount_amount' => $discount,
                    'used_at' => now(),
                ]);
                $coupon->increment('used_count');
            }

            return $order;
        });

        // Xóa session giỏ hàng và xóa các item đã mua khỏi DB cart
        $purchasedVariantIds = collect($cart)->pluck('variant_id')->filter()->all();
        session()->forget(['cart', 'cart_coupon']);
        if ($user && $user->cart) {
            if (!empty($purchasedVariantIds)) {
                $user->cart->items()->whereIn('product_variant_id', $purchasedVariantIds)->delete();
            } else {
                $user->cart->items()->delete();
            }
        }

        // 4. Phân luồng thanh toán
        if ($request->payment_method === 'momo') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'momo',
                'amount' => $order->total_price ?? $order->total,
                'status' => 'pending',
            ]);

            return redirect()->route('user.orders.momo.start', $order);
        }

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'amount' => $order->total_price ?? $order->total,
            'status' => 'pending',
            'message' => 'Thanh toán khi nhận hàng',
        ]);

        // --- NHÁNH COD: TẠO VẬN ĐƠN GHN NGAY LẬP TỨC ---
        $order->load('items.product');
        $ghnOrderResponse = $ghnOrders->create($order);

        if (($ghnOrderResponse['code'] ?? null) == 200 && !empty($ghnOrderResponse['data']['order_code'])) {
            $order->update([
                'status' => 'cod_ordered',
                'ghn_order_code' => $ghnOrderResponse['data']['order_code'],
                'shipping_status' => 'ready_to_pick',
            ]);

            return redirect()->route('user.orders.index')
                ->with('success', 'Đặt hàng thành công! Mã vận đơn GHN: ' . $ghnOrderResponse['data']['order_code']);
        }

        Log::error('GHN COD Order Failed: ', $ghnOrderResponse ?? []);
        $order->update(['status' => 'cod_ordered']);

        return redirect()->route('user.orders.index')
            ->with('warning', 'Đặt hàng thành công nhưng chưa thể tạo vận đơn GHN tự động.');
    }

    public function orderIndex(Request $request)
    {
        $orders = $request->user()->orders()->with(['items', 'paymentTransactions'])->latest()->paginate(15);

        return view('user.orders.index', compact('orders'));
    }

    public function getDistricts(Request $request, GHNService $ghn)
    {
        $provinceId = (int) $request->input('province_id', 202);
        $districts = $ghn->getDistricts($provinceId);

        return response()->json([
            'success' => true,
            'data' => $districts,
        ]);
    }

    public function getWards(Request $request, GHNService $ghn)
    {
        $districtId = (int) $request->input('district_id', 1442);
        $wards = $ghn->getWards($districtId);

        return response()->json([
            'success' => true,
            'data' => $wards,
        ]);
    }

    public function calculateFee(Request $request, GHNService $ghn)
    {
        $districtId = (int) $request->input('to_district_id', 1442);
        $wardCode = (string) $request->input('to_ward_code', '20308');

        $cart = session('cart', []);
        $totalWeight = collect($cart)->sum(fn($item) => $ghn->productWeight() * (int) $item['quantity']);

        $res = $ghn->calculateFee(array_merge([
            'from_district_id' => (int) config('services.ghn.from_district_id', 1442),
            'to_district_id' => $districtId,
            'to_ward_code' => $wardCode,
        ], $ghn->packageParameters($totalWeight)));

        $fee = (isset($res['code']) && $res['code'] == 200)
            ? (int) ($res['data']['total'] ?? 30000)
            : 30000;

        return response()->json([
            'success' => true,
            'shipping_fee' => $fee,
            'formatted' => number_format($fee, 0, ',', '.') . '₫',
        ]);
    }

    private function syncDbCartToSession($user, ?array $selectedItemIds = null): void
    {
        $sessionCart = [];
        $query = $user->cart?->items()->with(['variant.product.images', 'variant.color', 'variant.size']);
        if (!empty($selectedItemIds)) {
            $query->whereIn('id', $selectedItemIds);
        }
        $items = $query ? $query->get() : collect();

        foreach ($items as $item) {
            $product = $item->variant?->product;
            if (!$product) {
                continue;
            }
            $color = $item->variant?->color?->name ?? '';
            $colorHex = $item->variant?->color?->hex_code ?? null;
            $size = $item->variant?->size?->name ?? '';
            $variantLabel = trim($color . ($color && $size ? ' / ' : '') . $size);

            $primaryImg = $product->primary_image_url ?? ($product->images->first()?->image_url ?? asset('assets/images/product-fallback.svg'));

            $key = $item->product_variant_id ?? $product->id;
            $sessionCart[$key] = [
                'id' => $product->id,
                'variant_id' => $item->product_variant_id,
                'name' => $product->name,
                'product_name' => $product->name,
                'variant_label' => $variantLabel,
                'color' => $color,
                'color_hex' => $colorHex,
                'size' => $size,
                'sku' => $item->variant?->sku ?? $product->sku ?? ('SKU-' . $product->id),
                'image' => $primaryImg,
                'price' => (float) $product->current_price,
                'quantity' => (int) $item->quantity,
            ];
        }

        session(['cart' => $sessionCart]);
    }
}

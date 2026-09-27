<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GHNWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('GHN webhook received', $request->all());

        $ghnCode = $request->input('OrderCode') ?? $request->input('order_code');
        $clientCode = $request->input('ClientOrderCode') ?? $request->input('client_order_code');
        $status = strtolower(trim((string) ($request->input('Status') ?? $request->input('status') ?? '')));

        if (!$ghnCode && !$clientCode) {
            return response()->json(['message' => 'No order code provided'], 400);
        }

        $order = Order::where(function ($q) use ($ghnCode, $clientCode) {
            if ($ghnCode) {
                $q->where('ghn_order_code', $ghnCode);
            }
            if ($clientCode) {
                $q->orWhere('order_code', $clientCode);
            }
        })->first();

        if ($order && $status) {
            $shippingStatus = match ($status) {
                'ready_to_pick' => 'ready_to_pick',
                'picking' => 'picking',
                'picked', 'storing', 'transporting', 'sorting', 'delivering', 'money_collect_delivering' => 'delivering',
                'delivered' => 'delivered',
                'cancel' => 'cancelled',
                'return', 'returning', 'returned', 'return_transporting', 'return_sorting' => 'return',
                'delivery_fail' => 'delivery_fail',
                default => $status,
            };

            $updateData = ['shipping_status' => $shippingStatus];

            if ($shippingStatus === 'delivered') {
                $updateData['status'] = 'completed';
            } elseif ($shippingStatus === 'return') {
                $updateData['status'] = 'return';
            } elseif (in_array($shippingStatus, ['cancelled', 'delivery_fail'], true)) {
                $updateData['status'] = 'cancelled';
            }

            $order->update($updateData);

            Log::info("Order #{$order->id} updated via GHN webhook: shipping_status={$shippingStatus}");
        }

        return response()->json([
            'message' => 'Webhook received successfully',
            'data' => $request->all(),
        ]);
    }
}

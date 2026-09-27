<?php

namespace App\Services;

use App\Mail\OrderCancelledMail;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderConfirmedMail;
use App\Mail\OrderPlacedMail;
use App\Mail\OrderShippingMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderNotificationService
{
    public function placed(Order $order): void
    {
        $this->send($order, new OrderPlacedMail($this->withItems($order)), 'placed');
    }

    public function statusChanged(Order $order, string $from, string $to): void
    {
        $mail = match ([$from, $to]) {
            ['pending', 'confirmed'] => new OrderConfirmedMail($this->withItems($order)),
            ['packing', 'shipping'] => new OrderShippingMail($this->withItems($order)),
            ['shipping', 'completed'] => new OrderCompletedMail($this->withItems($order)),
            default => null,
        };

        if ($mail) {
            $this->send($order, $mail, $to);
        }
    }

    public function cancelled(Order $order): void
    {
        $this->send($order, new OrderCancelledMail($this->withItems($order)), 'cancelled');
    }

    private function withItems(Order $order): Order
    {
        return $order->loadMissing('items');
    }

    private function send(Order $order, object $mail, string $type): void
    {
        try {
            Mail::to($order->email)->send($mail);
        } catch (Throwable $exception) {
            Log::warning('Order email delivery failed.', [
                'order_code' => $order->order_code,
                'type' => $type,
                'exception' => $exception::class,
            ]);
        }
    }
}

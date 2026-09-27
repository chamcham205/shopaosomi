<?php
namespace App\Mail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class OrderShippingMail extends Mailable { use Queueable,SerializesModels; public function __construct(public Order $order){} public function build(){return $this->subject('SHIRT STORE - Đơn hàng đang được giao '.$this->order->order_code)->view('emails.orders.shipping');} }

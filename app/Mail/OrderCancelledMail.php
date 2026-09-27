<?php
namespace App\Mail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class OrderCancelledMail extends Mailable { use Queueable,SerializesModels; public function __construct(public Order $order){} public function build(){return $this->subject('SHIRT STORE - Đơn hàng đã hủy '.$this->order->order_code)->view('emails.orders.cancelled');} }

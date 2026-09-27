<?php
namespace App\Mail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class OrderCompletedMail extends Mailable { use Queueable,SerializesModels; public function __construct(public Order $order){} public function build(){return $this->subject('SHIRT STORE - Đơn hàng hoàn tất '.$this->order->order_code)->view('emails.orders.completed');} }

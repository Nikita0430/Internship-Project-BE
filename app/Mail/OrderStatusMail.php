<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $subject;
    public $order;

    public function __construct($order)
    {
        $this->subject = 'Order '.$order->order_no.' Update';
        $this->order = $order;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('Email.order_status');
    }
}

<?php

namespace App\Listeners;

use App\Events\OrderReceiptEvent;
use App\Mail\OrderReceiptMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderReceiptMail implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderReceiptEvent  $event
     * @return void
     */
    public function handle(OrderReceiptEvent $event)
    {
        Mail::to($event->booking->user->email)->send(new OrderReceiptMail($event->booking));
    }
}

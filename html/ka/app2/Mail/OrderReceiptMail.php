<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $booking;
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = 'no-reply@kirimaja.id';
        $subject = 'Bukti Bayar Sohib KirimAja';
        $name = 'KirimAja';

        return $this->view('emails.order_booking')
                    ->from($address, $name)
                    ->subject($subject)
                    ->with([ 
                        'booking' => $this->booking
                    ]);
    }
}

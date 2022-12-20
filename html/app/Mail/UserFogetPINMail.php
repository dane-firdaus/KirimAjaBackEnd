<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UserFogetPINMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $user, $pinCode;
    public function __construct($user, $pinCode)
    {
        $this->user = $user;
        $this->pinCode = $pinCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = 'no-reply@kirimaja.id';
        $subject = 'Token Lupa PIN';
        $name = 'KirimAja';

        return $this->view('emails.forget_pin')
                    ->from($address, $name)
                    ->subject($subject)
                    ->with([ 
                        'nama' => $this->user->fullname,
                        'token' => $this->pinCode,
                        'ka_icon' => url(Storage::url('appicon.png'))
                    ]);
    }
}

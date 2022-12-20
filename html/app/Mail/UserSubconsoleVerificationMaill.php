<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UserSubconsoleVerificationMaill extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $user, $verifyLink;
    public function __construct($user, $verifyLink)
    {
        $this->user = $user;
        $this->verifyLink = $verifyLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = 'no-reply@kirimaja.id';
        $subject = 'Selamat Datang Subconsole KirimAja';
        $name = 'KirimAja';

        return $this->view('emails.usersubconsole_verification')
                    ->from($address, $name)
                    ->subject($subject)
                    ->with([ 
                        'nama' => $this->user->fullname,
                        'verify_link' => $this->verifyLink,
                        'ka_icon' => url(Storage::url('appicon.png'))
                    ]);
    }
}

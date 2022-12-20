<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class UserForgetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $user, $forgetPasswordLink;
    public function __construct($user, $forgetPasswordLink)
    {
        $this->user = $user;
        $this->forgetPasswordLink = $forgetPasswordLink;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = 'no-reply@kirimaja.id';
        $subject = 'Reset Kata Sandi Sohib KirimAja';
        $name = 'KirimAja';

        return $this->view('emails.user_forget_password')
                    ->from($address, $name)
                    ->subject($subject)
                    ->with([
                        'nama' => $this->user->fullname, 
                        'forgetPasswordLink' => $this->forgetPasswordLink,
                        'ka_icon' => Storage::url('appicon.png')
                    ]);
    }
}

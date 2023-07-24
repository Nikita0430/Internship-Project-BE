<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    public $token;
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Builds the template for reset password email.
     *
     * @author growexx
     * @return static
     */
    public function build(){
        return $this->markdown('Email.resetPassword')->with([
            'token' => $this->token
        ])->subject('Reset Password');
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $subject;
    public $user;
    public $password;

    public function __construct($user, $password)
    {
        $this->subject = 'Welcome to Webociti';
        $this->user = $user;
        $this->password = $password;
    }

    public function build()
    {
        return $this->subject($this->subject)
            ->view('Email.new_user');
    }
}

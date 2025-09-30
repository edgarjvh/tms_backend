<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecoverPasswordMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $recovery_link;
    public $user_email_address;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recovery_link, $user_email_address)
    {
        $this->recovery_link = $recovery_link;
        $this->user_email_address = $user_email_address;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->to($this->user_email_address)
            ->subject('Password Recovery')
            ->view('mails.rate-conf.password_recovery_template');
    }
}

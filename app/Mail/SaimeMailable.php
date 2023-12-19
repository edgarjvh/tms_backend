<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaimeMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $last_status;
    public $date_time;
    public $body;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url,$last_status,$date_time,$body)
    {
        $this->url = $url;
        $this->last_status = $last_status;
        $this->date_time = $date_time;
        $this->body = $body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(env('CUSTOMER_CONF_EMAIL_SENDER'), 'Edgar Villasmil')
            ->to('edgarjvh@hotmail.com')
            ->subject("New Saime Status")
            ->view('mails.saime_template');
    }
}

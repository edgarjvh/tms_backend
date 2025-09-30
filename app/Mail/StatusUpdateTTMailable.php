<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusUpdateTTMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $orderNumber;
    public $status;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($orderNumber, $status)
    {
        $this->orderNumber = $orderNumber;
        $this->status = $status;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('bdoss@et3logistics.com', 'ET3 Logistics')
            ->to('bdoss@et3logistics.com')
            // ->cc($this->recipient_cc)
            ->bcc('edgarjvh@gmail.com')
            ->subject("Status Update on Load: $this->orderNumber")
            ->view('mails.rate-conf.status_update_trucker_tools');
    }
}

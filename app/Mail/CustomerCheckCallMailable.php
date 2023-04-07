<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerCheckCallMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $order_number;
    public $consignee_city;
    public $consignee_state;
    public $carrier_name;
    public $event_location;
    public $user_first_name;
    public $user_last_name;
    public $recipient_to;
    public $recipient_cc;
    public $recipient_bcc;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order_number, $consignee_city, $consignee_state, $carrier_name, $event_location, $user_first_name, $user_last_name, $recipient_to, $recipient_cc, $recipient_bcc)
    {
        $this->order_number = $order_number;
        $this->consignee_city = $consignee_city;
        $this->consignee_state = $consignee_state;
        $this->carrier_name = $carrier_name;
        $this->event_location = $event_location;
        $this->user_first_name = $user_first_name;
        $this->user_last_name = $user_last_name;
        $this->recipient_to = $recipient_to;
        $this->recipient_cc = $recipient_cc;
        $this->recipient_bcc = $recipient_bcc;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(env('CARRIER_PACKET_EMAIL_SENDER'), $this->user_first_name . ' ' . $this->user_last_name)
            ->to($this->recipient_to)
            ->cc($this->recipient_cc)
            ->bcc($this->recipient_bcc)
            ->subject("Check Call - Order $this->order_number")
            ->view('mails.rate-conf.customer_check_call_template');
    }
}

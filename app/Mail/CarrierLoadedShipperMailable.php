<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CarrierLoadedShipperMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $carrier_name;
    public $shipper_name;
    public $shipper_city;
    public $shipper_state;
    public $user_first_name;
    public $user_last_name;
    public $order_number;
    public $recipient_to;
    public $recipient_cc;
    public $recipient_bcc;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($carrier_name, $shipper_name, $shipper_city, $shipper_state, $user_first_name, $user_last_name, $order_number, $recipient_to, $recipient_cc, $recipient_bcc)
    {
        $this->carrier_name = $carrier_name;
        $this->shipper_name = $shipper_name;
        $this->shipper_city = $shipper_city;
        $this->shipper_state = $shipper_state;
        $this->user_first_name = $user_first_name;
        $this->user_last_name = $user_last_name;
        $this->order_number = $order_number;
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
            ->subject("Carrier Loaded - $this->order_number")
            ->view('mails.rate-conf.customer_carrier_loaded_shipper');
    }
}

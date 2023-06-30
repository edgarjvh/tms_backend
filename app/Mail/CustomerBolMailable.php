<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerBolMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $customer_first_name;
    public $origin_city;
    public $destination_city;
    public $origin_state;
    public $destination_state;
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
    public function __construct($customer_first_name, $origin_city, $origin_state, $destination_city, $destination_state, $user_first_name, $user_last_name, $order_number, $recipient_to, $recipient_cc, $recipient_bcc)
    {
        $this->customer_first_name = $customer_first_name;
        $this->origin_city = $origin_city;
        $this->origin_state = $origin_state;
        $this->destination_city = $destination_city;
        $this->destination_state = $destination_state;
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
            ->from(env('CUSTOMER_CONF_EMAIL_SENDER'), $this->user_first_name . ' ' . $this->user_last_name)
            ->to($this->recipient_to)
            ->cc($this->recipient_cc)
            ->bcc($this->recipient_bcc)
            ->subject("BOL - Order $this->order_number")
            ->view('mails.rate-conf.customer_bol_template');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarrierMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_mailing_addresses';

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }
}

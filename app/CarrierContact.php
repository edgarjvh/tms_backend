<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarrierContact extends Model
{
    protected $guarded = [];

    public function carrier(){
        return $this->belongsTo(Carrier::class)->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address']);
    }
}

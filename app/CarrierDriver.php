<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarrierDriver extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_drivers';

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }

    public function equipment(){
        return $this->belongsTo(Equipment::class);
    }
}

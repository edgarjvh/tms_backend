<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarrierNote extends Model
{
    protected $guarded = [];

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }
}

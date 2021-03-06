<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarrierDocument extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_documents';

    public function customer(){
        return $this->belongsTo(Carrier::class);
    }

    public function notes(){
        return $this->hasMany(CarrierDocumentNote::class);
    }
}

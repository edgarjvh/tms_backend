<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Carrier extends Model
{
    protected $guarded = [];

    public function contacts(){
        return $this->hasMany(CarrierContact::class);
    }

    public function drivers(){
        return $this->hasMany(CarrierDriver::class);
    }

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class);
    }
}
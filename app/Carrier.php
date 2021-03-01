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
        return $this->hasMany(CarrierDriver::class)->with(['equipment']);
    }

    public function factoring_company(){
        return $this->hasOne(FactoringCompany::class);
    }

    public function mailing_address(){
        return $this->hasOne(CarrierMailingAddress::class);
    }

    public function notes(){
        return $this->hasMany(CarrierNote::class);
    }

    public function insurances(){
        return $this->hasMany(Insurance::class)->with(['insurance_type']);
    }
}
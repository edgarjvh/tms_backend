<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $guarded = [];
    protected $table = 'drivers';

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }

    public function mailing_address(){
        return $this->hasOne(DriverMailingAddress::class, 'driver_id', 'id');
    }

    public function contacts(){
        return $this->hasMany(DriverEmergencyContact::class, 'driver_id', 'id')->with(['relationship'])->orderBy('priority');
    }

    public function license(){
        return $this->hasOne(DriverLicense::class, 'driver_id', 'id')->with([
            'class',
            'endorsement',
            'restriction',
            'driver'
        ]);
    }

    public function medical_card(){
        return $this->hasOne(DriverMedicalCard::class, 'driver_id', 'id')->with(['driver']);
    }

    public function tractor(){
        return $this->hasOne(DriverTractor::class, 'driver_id', 'id')->with(['type', 'driver']);
    }

    public function trailer(){
        return $this->hasOne(DriverTrailer::class, 'driver_id', 'id')->with(['type', 'driver']);
    }

    public function equipment(){
        return $this->belongsTo(Equipment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriver extends Model
{
    protected $guarded = [];
    protected $table = 'company_drivers';

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function mailing_address(){
        return $this->hasOne(CompanyDriverMailingAddress::class, 'company_driver_id', 'id');
    }

    public function contacts(){
        return $this->hasMany(CompanyDriverEmergencyContact::class, 'driver_id', 'id')->orderBy('priority');
    }

    public function license(){
        return $this->hasOne(CompanyDriverLicense::class, 'company_driver_id', 'id')->with([
            'class',
            'endorsement',
            'restriction',
            'company_driver'
        ]);
    }

    public function medical_card(){
        return $this->hasOne(CompanyDriverMedicalCard::class, 'company_driver_id', 'id')->with(['company_driver']);
    }

    public function tractor(){
        return $this->hasOne(CompanyDriverTractor::class, 'company_driver_id', 'id')->with(['type', 'company_driver']);
    }

    public function trailer(){
        return $this->hasOne(CompanyDriverTrailer::class, 'company_driver_id', 'id')->with(['type', 'company_driver']);
    }
}

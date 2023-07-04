<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperator extends Model
{
    protected $guarded = [];
    protected $table = 'company_operators';

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function mailing_address(){
        return $this->hasOne(CompanyOperatorMailingAddress::class, 'company_operator_id', 'id');
    }

    public function contacts(){
        return $this->hasMany(CompanyOperatorEmergencyContact::class, 'operator_id', 'id')->orderBy('priority');
    }

    public function license(){
        return $this->hasOne(CompanyOperatorLicense::class, 'company_operator_id', 'id')->with([
            'class',
            'endorsement',
            'restriction'
        ]);
    }

    public function medical_card(){
        return $this->hasOne(CompanyOperatorMedicalCard::class, 'company_operator_id', 'id');
    }

    public function tractor(){
        return $this->hasOne(CompanyOperatorTractor::class, 'company_operator_id', 'id')->with(['type']);
    }

    public function trailer(){
        return $this->hasOne(CompanyOperatorTrailer::class, 'company_operator_id', 'id')->with(['type']);
    }
}

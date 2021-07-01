<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompany extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_companies';

    public function carriers(){
        return $this->hasMany(Carrier::class);
    }

    public function notes(){
        return $this->hasMany(FactoringCompanyNote::class);
    }

    public function contacts(){
        return $this->hasMany(FactoringCompanyContact::class);
    }

    public function invoices(){
        return $this->hasMany(FactoringCompanyInvoice::class);
    }

    public function mailing_address(){
        return $this->hasOne(FactoringCompanyMailingAddress::class)->with(['mailing_contact']);
    }

    public function documents(){
        return $this->hasMany(FactoringCompanyDocument::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompanyMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_mailing_addresses';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class);
    }

    public function mailing_contact(){
        return $this->belongsTo(FactoringCompanyContact::class,'mailing_contact_id', 'id', 'factoring_company_contacts');
    }
}

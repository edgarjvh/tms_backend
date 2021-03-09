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
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompanyContact extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_contacts';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class)->with(['documents','contacts', 'invoices', 'carriers', 'mailing_address', 'notes']);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompanyInvoice extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_invoices';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class);
    }
}

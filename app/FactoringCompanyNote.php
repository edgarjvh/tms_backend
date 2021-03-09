<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompanyNote extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_notes';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class);
    }
}

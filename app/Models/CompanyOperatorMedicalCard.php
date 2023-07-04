<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorMedicalCard extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_medical_cards';

    public function company_operator(){
        return $this->belongsTo(CompanyOperator::class, 'company_operator_id', 'id');
    }
}

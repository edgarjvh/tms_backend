<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_mailing_addresses';

    public function company_operator(){
        return $this->belongsTo(CompanyOperator::class, 'company_operator_id', 'id');
    }
}

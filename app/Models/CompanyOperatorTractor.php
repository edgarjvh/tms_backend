<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorTractor extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_tractors';

    public function company_operator(){
        return $this->belongsTo(CompanyOperator::class, 'company_operator_id', 'id');
    }

    public function type(){
        return $this->belongsTo(Equipment::class, 'type_id', 'id');
    }
}

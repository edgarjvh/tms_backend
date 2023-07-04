<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorTrailer extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_trailers';

    public function company_operator(){
        return $this->belongsTo(CompanyOperator::class, 'company_operator_id', 'id');
    }

    public function type(){
        return $this->belongsTo(Equipment::class, 'type_id', 'id');
    }
}

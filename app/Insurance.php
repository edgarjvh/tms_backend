<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $guarded = [];

    public function insuranceType(){
        return $this->belongsTo(InsuranceType::class);
    }
}

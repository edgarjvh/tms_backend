<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $guarded = [];

    public function insurance_type(){
        return $this->belongsTo(InsuranceType::class);
    }

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }
}

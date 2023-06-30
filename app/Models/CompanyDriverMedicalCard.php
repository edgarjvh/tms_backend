<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverMedicalCard extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_medical_cards';

    public function company_driver(){
        return $this->belongsTo(CompanyDriver::class, 'company_driver_id', 'id');
    }
}

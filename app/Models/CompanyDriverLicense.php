<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverLicense extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_licenses';

    public function company_driver(){
        return $this->belongsTo(CompanyDriver::class, 'company_driver_id', 'id');
    }

    public function class(){
        return $this->belongsTo(LicenseClass::class, 'class_id', 'id');
    }

    public function endorsement(){
        return $this->belongsTo(LicenseEndorsement::class, 'endorsement_id', 'id');
    }

    public function restriction(){
        return $this->belongsTo(LicenseRestriction::class, 'restriction_id', 'id');
    }
}

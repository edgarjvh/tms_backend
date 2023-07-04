<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorLicense extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_licenses';

    public function company_operator(){
        return $this->belongsTo(CompanyOperator::class, 'company_operator_id', 'id');
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

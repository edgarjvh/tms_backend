<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLicense extends Model
{
    protected $guarded = [];
    protected $table = 'driver_licenses';

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
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

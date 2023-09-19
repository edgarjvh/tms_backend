<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseRestriction extends Model
{
    protected $guarded = [];
    protected $table = 'license_restrictions';

    public function licenses(){
        return $this->hasMany(DriverLicense::class, 'restriction_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseEndorsement extends Model
{
    protected $guarded = [];
    protected $table = 'license_endorsements';

    public function licenses(){
        return $this->hasMany(DriverLicense::class, 'endorsement_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseClass extends Model
{
    protected $guarded = [];
    protected $table = 'license_classes';

    public function licenses(){
        return $this->hasMany(DriverLicense::class, 'class_id', 'id');
    }
}

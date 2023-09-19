<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Relationship extends Model
{
    protected $guarded = [];
    protected $table = 'relationships';

    public function emergency_contacts(){
        return $this->hasMany(DriverEmergencyContact::class);
    }
}

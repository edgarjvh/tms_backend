<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverMedicalCard extends Model
{
    protected $guarded = [];
    protected $table = 'driver_medical_cards';

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}

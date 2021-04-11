<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $guarded = [];
    protected $table = 'equipments';

    public function drivers(){
        return $this->hasMany(CarrierDriver::class);
    }
}

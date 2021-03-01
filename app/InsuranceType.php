<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InsuranceType extends Model
{
    protected $guarded = [];

    public function insurance(){
        return $this->hasMany(Insurance::class);
    }
}

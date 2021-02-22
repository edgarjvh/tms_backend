<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompany extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_companies';

    public function carriers(){
        return $this->hasMany(Carrier::class);
    }
}

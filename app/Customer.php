<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    public function contacts(){
        return $this->hasMany(Contact::class);
    }

    public function documents(){
        return $this->hasMany(CustomerDocument::class);
    }
}

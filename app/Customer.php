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
        return $this->hasMany(CustomerDocument::class)->with('notes');
    }

    public function directions(){
        return $this->hasMany(Direction::class);
    }

    public function hours(){
        return $this->hasOne(CustomerHour::class);
    }

    public function automaticEmails(){
        return $this->hasOne(AutomaticEmail::class);
    }

    public function notes(){
        return $this->hasMany(Note::class);
    }
}

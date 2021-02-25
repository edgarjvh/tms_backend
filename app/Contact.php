<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $guarded = [];

    public function customer(){
        return $this->belongsTo(Customer::class)->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes']);
    }
}

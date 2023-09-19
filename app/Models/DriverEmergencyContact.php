<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverEmergencyContact extends Model
{
    protected $guarded = [];
    protected $table = 'contacts';
    protected $appends = ['name'];

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function relationship(){
        return $this->belongsTo(Relationship::class);
    }
}

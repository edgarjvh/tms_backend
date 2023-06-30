<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverEmergencyContact extends Model
{
    protected $guarded = [];
    protected $table = 'contacts';
    protected $appends = ['name'];

    public function company_driver()
    {
        return $this->belongsTo(CompanyDriver::class, 'driver_id', 'id');
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}

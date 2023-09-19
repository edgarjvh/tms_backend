<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'driver_mailing_addresses';

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverTrailer extends Model
{
    protected $guarded = [];
    protected $table = 'driver_trailers';

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function type(){
        return $this->belongsTo(Equipment::class, 'type_id', 'id');
    }
}

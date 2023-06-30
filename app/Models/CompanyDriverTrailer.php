<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverTrailer extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_trailers';

    public function company_driver(){
        return $this->belongsTo(CompanyDriver::class, 'company_driver_id', 'id');
    }

    public function type(){
        return $this->belongsTo(Equipment::class, 'type_id', 'id');
    }
}

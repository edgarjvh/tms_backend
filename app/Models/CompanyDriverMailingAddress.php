<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_mailing_addresses';

    public function company_driver(){
        return $this->belongsTo(CompanyDriver::class, 'company_driver_id', 'id');
    }
}

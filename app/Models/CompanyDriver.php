<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDriver extends Model
{
    protected $guarded = [];
    protected $table = 'company_drivers';

    public function company(){
        return $this->belongsTo(Company::class)->with(['drivers']);
    }
}

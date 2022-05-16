<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];
    protected $table = 'company_employees';

    public function company(){
        return $this->belongsTo(Company::class)->with(['employees']);
    }
}

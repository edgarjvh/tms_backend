<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyDriver extends Model
{
    protected array $guarded = [];
    protected string $table = 'company_drivers';

    public function company(){
        return $this->belongsTo(Company::class)->with(['drivers']);
    }

    public function documents(){
        return $this->hasMany(DriverDocument::class)->with(['notes', 'user_code']);
    }
}

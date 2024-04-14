<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    use HasApiTokens;

    protected $guarded = [];
    protected $table = 'company_employees';
    protected $hidden = [
        'password'
    ];

    public function company(){
        return $this->belongsTo(Company::class)->with(['employees']);
    }

    public function documents(){
        return $this->hasMany(EmployeeDocument::class)->with(['notes', 'user_code']);
    }

    public function user_code(){
        return $this->hasOne(UserCode::class)->with(['permissions', 'widgets']);
    }

    public function getAuthPassword(){
        return $this->password;
    }
}

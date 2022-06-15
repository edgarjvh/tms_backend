<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        return $this->hasMany(EmployeeDocument::class)->with(['notes']);
    }

    public function getAuthPassword(){
        return $this->password;
    }
}

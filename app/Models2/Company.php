<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected array $guarded = [];
    protected string $table = 'companies';

    public function employees() {
        return $this->hasMany(Employee::class, 'company_id', 'id');
    }

    public function agents(){
        return $this->hasMany(Agent::class, 'company_id', 'id');
    }

    public function drivers(){
        return $this->hasMany(CompanyDriver::class, 'company_id', 'id');
    }

    public function operators(){
        return $this->hasMany(OwnerOperator::class, 'company_id', 'id');
    }
}

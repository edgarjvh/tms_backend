<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    protected $guarded = [];
    protected $table = 'company_employees';

    public function customers(){
        return $this->hasMany(Customer::class, 'salesman_id', 'id');
    }
}

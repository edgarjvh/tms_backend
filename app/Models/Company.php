<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $guarded = [];
    protected $table = 'companies';

    public function mailing_same()
    {
        return $this->belongsTo(Company::class, 'id', 'id')->where('mailing_address_is_the_same', 1);
    }

    public function mailing_address()
    {
        return $this->belongsTo(CompanyMailingAddress::class, 'mailing_address_id', 'id');
    }

    public function employees() {
        return $this->hasMany(Employee::class, 'company_id', 'id')->with(['documents'])->orderBy('id');
    }

    public function agents(){
        return $this->hasMany(Agent::class, 'company_id', 'id')->with(['contacts'])->orderBy('id');
    }

    public function drivers(){
        $instance = $this->hasMany(Driver::class);
        $instance->getQuery()
            ->whereRaw("LOWER(LEFT(code, 2)) = 'cd'")
            ->with(['contacts'])
            ->orderBy('id');
        return $instance;
    }

    public function operators(){
        $instance = $this->hasMany(Driver::class);
        $instance->getQuery()
            ->whereRaw("LOWER(LEFT(code, 2)) = 'op'")
            ->whereDoesntHave('agent')
            ->with(['contacts'])
            ->orderBy('id');
        return $instance;
    }
}

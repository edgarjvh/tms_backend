<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    use HasApiTokens;

    protected $guarded = [];
    protected $table = 'company_agents';
    protected $hidden = [
        'password'
    ];

    public function company(){
        return $this->belongsTo(Company::class)->with(['agents']);
    }

    public function getAuthPassword(){
        return $this->password;
    }
}

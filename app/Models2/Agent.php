<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Authenticatable
{
    use HasApiTokens;

    protected array $guarded = [];
    protected string $table = 'company_agents';
    protected $hidden = [
        'password'
    ];

    public function company(){
        return $this->belongsTo(Company::class)->with(['agents']);
    }

    public function documents(){
        return $this->hasMany(AgentDocument::class)->with(['notes', 'user_code']);
    }

    public function user_code(){
        return $this->hasOne(UserCode::class);
    }

    public function getAuthPassword(){
        return $this->password;
    }
}

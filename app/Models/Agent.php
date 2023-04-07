<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agent extends Model
{
    use HasApiTokens;

    protected $guarded = [];
    protected $table = 'company_agents';
    protected $hidden = [
        'password'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->with(['agents']);
    }

    public function documents()
    {
        return $this->hasMany(AgentDocument::class)->with(['notes', 'user_code']);
    }

    public function contacts()
    {
        return $this->hasMany(AgentContact::class)->orderBy('first_name')->orderBy('last_name');
    }

    public function mailing_address(){
        return $this->hasOne(AgentMailingAddress::class)->with(['mailing_contact']);
    }

    public function notes(){
        return $this->hasMany(AgentNote::class)->with(['user_code']);
    }

    public function hours(){
        return $this->hasOne(AgentHour::class);
    }

    public function drivers(){
        return $this->hasMany(AgentDriver::class)->with(['equipment']);
    }

    public function division(){
        return $this->belongsTo(Division::class);
    }

    public function user_code()
    {
        return $this->hasOne(UserCode::class);
    }
}

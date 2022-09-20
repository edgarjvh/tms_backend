<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class AgentContact extends Authenticatable
{
    use HasApiTokens;

    protected $guarded = [];
    protected $table = 'contacts';
    protected $hidden = [
        'password'
    ];

    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    public function user_code(){
        return $this->hasOne(UserCode::class)->with('permissions');
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}

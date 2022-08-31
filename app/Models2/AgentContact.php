<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Post
 *
 * @mixin Builder
 */
class AgentContact extends Authenticatable
{
    use HasApiTokens;

    protected array $guarded = [];
    protected string $table = 'contacts';
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

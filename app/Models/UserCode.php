<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */

class UserCode extends Model
{
    protected $guarded = [];
    protected $table = 'user_codes';

    public function employee(){
        return $this->belongsTo(Employee::class);
    }

    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    function permissions(){
        return $this->belongsToMany(Permission::class)->withPivot(['read','save','edit','delete']);
    }
}

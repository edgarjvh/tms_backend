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

    public function widgets(){
        return $this->belongsToMany(Widget::class)->withPivot(['top', 'left']);
    }

    function permissions(){
        return $this->belongsToMany(Permission::class)->withPivot(['read','save','edit','delete']);
    }
}

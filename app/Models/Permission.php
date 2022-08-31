<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
/**
 * Post
 *
 * @mixin Builder
 */
class Permission extends Model
{
    protected $guarded = [];
    protected $table = 'permissions';

    function user_codes(){
        return $this->belongsToMany(UserCode::class)->withPivot(['read','save','edit','delete']);
    }
}

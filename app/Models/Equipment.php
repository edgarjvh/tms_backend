<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Equipment extends Model
{
    protected $guarded = [];
    protected $table = 'equipments';

    public function drivers(){
        return $this->hasMany(CarrierDriver::class);
    }
}

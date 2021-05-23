<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pickup extends Model
{
    protected $guarded = [];
    protected $table = 'order_pickups';
}

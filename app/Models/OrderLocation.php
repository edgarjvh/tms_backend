<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLocation extends Model
{
    protected $guarded = [];
    protected $table = 'order_locations';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderComment extends Model
{
    protected $guarded = [];
    protected $table = 'order_comments';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}

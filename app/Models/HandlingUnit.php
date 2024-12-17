<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HandlingUnit extends Model
{
    protected $table = 'handling_units';
    protected $guarded = [];

    public function order_ltl_unit()
    {
        return $this->belongsTo(OrderLtlUnit::class, 'handling_unit_id', 'id');
    }
}

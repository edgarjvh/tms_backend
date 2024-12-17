<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HazmatPackaging extends Model
{
    protected $table = 'handling_units';
    protected $guarded = [];

    public function order_ltl_unit()
    {
        return $this->belongsTo(OrderLtlUnit::class, 'hazmat_packaging_id', 'id');
    }
}

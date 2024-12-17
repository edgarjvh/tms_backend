<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitClass extends Model
{
    protected $table = 'unit_classes';
    protected $guarded = [];

    public function order_ltl_unit()
    {
        return $this->belongsTo(OrderLtlUnit::class, 'unit_class_id', 'id');
    }
}

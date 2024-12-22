<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HazmatClass extends Model
{
    protected $table = 'hazmat_classes';
    protected $guarded = [];

    public function order_ltl_unit()
    {
        return $this->belongsTo(OrderLtlUnit::class, 'hazmat_class_id', 'id');
    }

    public function hazmat()
    {
        return $this->belongsTo(Hazmat::class, 'hazmat_class_id', 'id');
    }
}

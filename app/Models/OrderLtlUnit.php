<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderLtlUnit
 *
 * Represents a note in the system.
 *
 * @package App\Models
 */


class OrderLtlUnit extends Model
{
    protected $guarded = [];
    protected $table = 'order_ltl_units';

    /**
     * Get the order that belongs to this instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function handling_unit(){
        return $this->hasOne(HandlingUnit::class, 'id', 'handling_unit_id');
    }

    public function unit_class(){
        return $this->hasOne(UnitClass::class, 'id', 'unit_class_id');
    }

    public function hazmat_packaging(){
        return $this->hasOne(HazmatPackaging::class, 'id', 'hazmat_packaging_id');
    }

    public function hazmat_class(){
        return $this->hasOne(HazmatClass::class, 'id', 'hazmat_class_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */
class Route extends Model
{
    protected $guarded = [];
    protected $table = 'order_routing';

    public function customer(){
        return $this->belongsTo(Customer::class)
            ->with(['contacts', 'zip_data']);
    }

    public function pickup(){
        return $this->belongsTo(Pickup::class);
    }

    public function delivery(){
        return $this->belongsTo(Delivery::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */

class OrderCarrierRating extends Model
{
    protected array $guarded = [];
    protected string $table = 'order_carrier_ratings';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function rate_type(){
        return $this->belongsTo(RateType::class);
    }

    public function rate_subtype(){
        return $this->belongsTo(RateSubtype::class);
    }
}

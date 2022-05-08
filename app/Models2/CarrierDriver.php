<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CarrierDriver extends Model
{
    protected array $guarded = [];
    protected string $table = 'carrier_drivers';

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }

    public function equipment(){
        return $this->belongsTo(Equipment::class);
    }
}

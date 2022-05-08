<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Insurance extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_insurances';

    public function insurance_type(){
        return $this->belongsTo(InsuranceType::class);
    }

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }
}

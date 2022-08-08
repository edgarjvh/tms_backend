<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CarrierNote extends Model
{
    protected $guarded = [];

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

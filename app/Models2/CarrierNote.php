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
    protected array $guarded = [];

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }
}

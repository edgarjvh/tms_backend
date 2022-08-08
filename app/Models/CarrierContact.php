<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CarrierContact extends Model
{
    protected $guarded = [];
    protected $table = 'contacts';

    public function carrier(){
        return $this->belongsTo(Carrier::class)->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address']);
    }
}

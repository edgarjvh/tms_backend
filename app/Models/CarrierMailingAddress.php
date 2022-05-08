<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CarrierMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_mailing_addresses';

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }

    public function mailing_contact(){
        return $this->belongsTo(CarrierContact::class,'mailing_contact_id', 'id', 'carrier_contacts');
    }
}

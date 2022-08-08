<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */
class DivisionMailingAddress extends Model
{
    use \Awobaz\Compoships\Compoships;

    protected $guarded = [];
    protected $table = 'division_mailing_addresses';

    public function division(){
        return $this->belongsTo(Division::class);
    }

    public function mailing_contact(){
        return $this->belongsTo(DivisionContact::class,'mailing_contact_id', 'id', 'contacts');
    }
}

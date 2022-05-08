<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class FactoringCompanyMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_mailing_addresses';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class);
    }

    public function mailing_contact(){
        return $this->belongsTo(FactoringCompanyContact::class,'mailing_contact_id', 'id', 'factoring_company_contacts');
    }
}

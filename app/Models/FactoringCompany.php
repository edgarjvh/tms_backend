<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class FactoringCompany extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_companies';

    public function carriers(){
        return $this->hasMany(Carrier::class);
    }

    public function notes(){
        return $this->hasMany(FactoringCompanyNote::class)->with(['user_code']);
    }

    public function contacts(){
        return $this->hasMany(Contact::class)->orderBy('first_name', 'asc');
    }

    public function invoices(){
        return $this->hasMany(FactoringCompanyInvoice::class);
    }

    public function mailing_address(){
        return $this->hasOne(FactoringCompanyMailingAddress::class)->with(['mailing_contact']);
    }

    public function documents(){
        return $this->hasMany(FactoringCompanyDocument::class)->with(['notes', 'user_code']);
    }
}

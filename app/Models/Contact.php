<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Contact extends Model
{
    protected $guarded = [];
    protected $table = 'contacts';

    public function customer(){
        return $this->belongsTo(Customer::class)->with(['documents', 'directions', 'hours', 'automatic_emails', 'notes']);
    }

    public function carrier(){
        return $this->belongsTo(Carrier::class)->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address']);
    }

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class)->with(['documents','contacts', 'invoices', 'carriers', 'mailing_address', 'notes']);
    }

    public function ext_customers(){
        return $this->belongsToMany(Customer::class);
    }
}

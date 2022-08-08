<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Carrier extends Model
{
    protected $guarded = [];

    public function contacts(){
        return $this->hasMany(Contact::class)->orderBy('first_name', 'asc');
    }

    public function drivers()
    {
        return $this->hasMany(CarrierDriver::class)->with(['equipment']);
    }

    public function factoring_company()
    {
        return $this->belongsTo(FactoringCompany::class)->with([
            'carriers',
            'contacts',
            'invoices',
            'mailing_address',
            'notes',
            'documents'
        ]);
    }

    public function mailing_address()
    {
        return $this->hasOne(CarrierMailingAddress::class)->with(['mailing_contact']);
    }

    public function notes()
    {
        return $this->hasMany(CarrierNote::class)->with(['user_code']);
    }

    public function insurances()
    {
        return $this->hasMany(Insurance::class)->with(['insurance_type']);
    }

    public function documents(){
        return $this->hasMany(CarrierDocument::class)->with(['notes', 'user_code']);
    }

    public function equipments_information(){
        return $this->hasMany(CarrierEquipment::class)->with(['equipment']);
    }
}

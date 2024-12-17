<?php

namespace App\Models;

use DateTime;
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
    protected $appends = ['insurance_status', 'insurance_remaining_days'];

    public function getInsuranceStatusAttribute()
    {
        $currentDate = DateTime::createFromFormat('m/d/Y', date('m/d/Y'));
        $nextMonthDate = (clone $currentDate)->modify('+1 month');

        $insurances = $this->insurances;
        $status = '';
        if ($insurances->count() > 0) {            
            foreach ($insurances as $insurance) {
                $expirationDate = DateTime::createFromFormat('m/d/Y', $insurance->expiration_date);
                if ($expirationDate < $currentDate) {
                    $status = 'expired';
                } else if ($expirationDate >= $currentDate && $expirationDate <= $nextMonthDate) {
                    if ($status !== 'expired') {
                        $status = 'warning';
                    }
                } else{
                    if ($status !== 'expired' && $status !== 'warning') {
                        $status = 'active';
                    }
                }
            }
        }

        return $status;
    }

    public function getInsuranceRemainingDaysAttribute()
    {        
        $currentDate = DateTime::createFromFormat('m/d/Y', date('m/d/Y'));
        $nextExpiringInsurance = null;

        foreach ($this->insurances as $insurance) {
            $expirationDate = DateTime::createFromFormat('m/d/Y', $insurance->expiration_date);
            if ($expirationDate > $currentDate && 
                ($nextExpiringInsurance === null || $expirationDate < DateTime::createFromFormat('m/d/Y', $nextExpiringInsurance->expiration_date))) {
                $nextExpiringInsurance = $insurance;
            }
        }

        if ($nextExpiringInsurance) {
            $expirationDate = DateTime::createFromFormat('m/d/Y', $nextExpiringInsurance->expiration_date);
            $interval = $currentDate->diff($expirationDate);
            return $interval->days;
        }

        return null;
    }

    public function mailing_same()
    {
        return $this->belongsTo(Carrier::class, 'id', 'id')->where('remit_to_address_is_the_same', 1);
    }

    public function mailing_address()
    {
        return $this->belongsTo(CarrierMailingAddress::class, 'mailing_address_id', 'id');
    }

    public function mailing_carrier()
    {
        return $this->belongsTo(Carrier::class, 'mailing_carrier_id', 'id')->with(['contacts']);
    }

    public function contacts(){
        return $this->hasMany(Contact::class)->orderBy('first_name')->orderBy('last_name');
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class)->with(['contacts','tractor','trailer','equipment']);
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

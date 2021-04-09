<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];
    protected $table = 'orders';

    public function bill_to_company()
    {
        return $this->belongsTo(Customer::class, 'bill_to_customer_id', 'id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes']);
    }

    public function shipper_company()
    {
        return $this->belongsTo(Customer::class, 'shipper_customer_id', 'id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes']);
    }

    public function consignee_company()
    {
        return $this->belongsTo(Customer::class, 'consignee_customer_id', 'id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes']);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class)
            ->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address']);
    }

    public function driver(){
        return $this->belongsTo(CarrierDriver::class, 'carrier_driver_id', 'id')->with(['equipment']);
    }

    public function notes_for_carrier(){
        return $this->hasMany(NotesForCarrier::class, 'order_number', 'order_number');
    }

    public function internal_notes(){
        return $this->hasMany(InternalNotes::class, 'order_number', 'order_number');
    }
}

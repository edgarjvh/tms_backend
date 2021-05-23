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
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data']);
    }

    public function shipper_company()
    {
        return $this->belongsTo(Customer::class, 'shipper_customer_id', 'id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data']);
    }

    public function consignee_company()
    {
        return $this->belongsTo(Customer::class, 'consignee_customer_id', 'id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data']);
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

    public function pickups () {
        return $this->belongsToMany(Customer::class, 'order_pickups', 'order_id', 'customer_id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data'])
            ->withPivot([
                'pu_date1',
                'pu_date2',
                'pu_time1',
                'pu_time2',
                'bol_numbers',
                'po_numbers',
                'ref_numbers',
                'seal_number',
                'special_instructions',
                'carried_out',
                'type'
            ])->as('extra_data')
            ->withTimestamps();
    }

    public function deliveries () {
        return $this->belongsToMany(Customer::class, 'order_deliveries', 'order_id', 'customer_id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data'])
            ->withPivot([
                'delivery_date1',
                'delivery_date2',
                'delivery_time1',
                'delivery_time2',
                'special_instructions',
                'type'
            ])->as('extra_data')
            ->withTimestamps();
    }

    public function routing (){
        return $this->belongsToMany(Customer::class, 'order_routing', 'order_id', 'customer_id')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data'])
            ->withPivot([
                'type'
            ])->as('extra_data')
            ->withTimestamps();
    }

    public function documents(){
        return $this->hasMany(OrderDocument::class)->with('notes');
    }

    public function events(){
        return $this->hasMany(OrderEvent::class)
            ->with(['shipper', 'consignee', 'old_carrier', 'new_carrier']);
    }
}

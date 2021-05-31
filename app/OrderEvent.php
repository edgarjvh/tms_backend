<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderEvent extends Model
{
    protected $guarded = [];
    protected $table = 'order_events';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function shipper(){
        return $this->belongsTo(Customer::class, 'shipper_id', 'id', 'customers')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data']);
    }

    public function consignee(){
        return $this->belongsTo(Customer::class, 'consignee_id', 'id', 'customers')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data']);
    }

    public function arrived_customer(){
        return $this->belongsTo(Customer::class, 'arrived_customer_id', 'id', 'customers')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data']);
    }

    public function departed_customer(){
        return $this->belongsTo(Customer::class, 'departed_customer_id', 'id', 'customers')
            ->with(['contacts', 'documents', 'directions', 'hours', 'automaticEmails', 'notes', 'zip_data']);
    }

    public function old_carrier(){
        return $this->belongsTo(Carrier::class, 'old_carrier_id', 'id', 'carriers')
            ->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address']);
    }

    public function new_carrier(){
        return $this->belongsTo(Carrier::class, 'new_carrier_id', 'id', 'carriers')
            ->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address']);
    }
}

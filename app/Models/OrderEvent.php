<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class OrderEvent extends Model
{
    protected $guarded = [];
    protected $table = 'order_events';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function shipper(){
        return $this->belongsTo(Customer::class, 'shipper_id', 'id', 'customers')
            ->with(['zip_data']);
    }

    public function consignee(){
        return $this->belongsTo(Customer::class, 'consignee_id', 'id', 'customers')
            ->with(['zip_data']);
    }

    public function arrived_customer(){
        return $this->belongsTo(Customer::class, 'arrived_customer_id', 'id', 'customers')
            ->with(['zip_data']);
    }

    public function departed_customer(){
        return $this->belongsTo(Customer::class, 'departed_customer_id', 'id', 'customers')
            ->with(['zip_data']);
    }

    public function old_carrier(){
        return $this->belongsTo(Carrier::class, 'old_carrier_id', 'id', 'carriers')
            ->with(['drivers', 'insurances']);
    }

    public function new_carrier(){
        return $this->belongsTo(Carrier::class, 'new_carrier_id', 'id', 'carriers')
            ->with(['drivers', 'insurances']);
    }

    public function event_type(){
        return $this->belongsTo(EventType::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

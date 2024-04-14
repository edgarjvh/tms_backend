<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */
class Pickup extends Model
{
    protected $guarded = [];
    protected $table = 'order_pickups';

    public function customer(){
        return $this->belongsTo(Customer::class)
            ->with(['zip_data', 'directions']);
    }

    public function customerPO()
    {
        return $this->belongsTo(PastOrderCustomer::class)
            ->select(['id', 'city', 'state']);
    }

    public function order(){
        return $this->belongsTo(Order::class)
            ->with([
                'bill_to_company',
                'carrier',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'documents',
                'events',
                'division',
                'load_type',
                'template',
                'order_customer_ratings',
                'order_carrier_ratings',
            ]);
    }
}

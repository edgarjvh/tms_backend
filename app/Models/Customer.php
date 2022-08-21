<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Post
 *
 * @mixin Builder
 */

class Customer extends Model
{
    use HasRelationships;
    use Compoships;

    protected $guarded = [];
    protected $table = 'customers';
    protected $appends = ['total_customer_order', 'credit_ordered', 'credit_invoiced', 'credit_paid'];

    public function total_customer_ratings(){
        return $this->hasManyDeep(OrderCustomerRating::class, [Order::class], ['bill_to_customer_id'], ['id'])
            ->whereRaw('orders.is_imported = 0');
    }

    public function total_credit_ordered_ratings(){
        return $this->hasManyDeep(OrderCustomerRating::class, [Order::class], ['bill_to_customer_id'], ['id'])
            ->whereRaw('orders.is_imported = 0')
            ->whereRaw('orders.customer_check_number IS NULL')
            ->whereRaw('orders.order_invoiced = 0');
    }

    public function getCreditOrderedAttribute(){
        return $this->total_credit_ordered_ratings()->sum('total_charges');
    }

    public function total_credit_invoiced_ratings(){
        return $this->hasManyDeep(OrderCustomerRating::class, [Order::class], ['bill_to_customer_id'], ['id'])
            ->whereRaw('orders.is_imported = 0')
            ->whereRaw('orders.customer_check_number IS NULL')
            ->whereRaw('orders.order_invoiced = 1');
    }

    public function getCreditInvoicedAttribute(){
        return $this->total_credit_invoiced_ratings()->sum('total_charges');
    }

    public function total_credit_paid_ratings(){
        return $this->hasManyDeep(OrderCustomerRating::class, [Order::class], ['bill_to_customer_id'], ['id'])
            ->whereRaw('orders.is_imported = 0')
            ->whereRaw('orders.customer_check_number IS NOT NULL');
    }

    public function getCreditPaidAttribute(){
        return $this->total_credit_paid_ratings()->sum('total_charges');
    }

    public function getTotalCustomerOrderAttribute(){
        return $this->total_customer_ratings()->sum('total_charges');
    }

    public function contacts(){
        return $this->hasMany(Contact::class)->orderBy('first_name', 'asc');
    }

    public function documents(){
        return $this->hasMany(CustomerDocument::class)->with(['notes', 'user_code']);
    }

    public function directions(){
        return $this->hasMany(Direction::class)->with(['user_code']);
    }

    public function hours(){
        return $this->hasOne(CustomerHour::class);
    }

    public function automatic_emails(){
        return $this->hasMany(AutomaticEmail::class);
    }

    public function notes(){
        return $this->hasMany(Note::class)->with(['user_code']);
    }

    public function zip_data(){
        return $this->belongsTo(ZipCode::class,'zip','zip_code', 'us_zipcodes');
    }

    public function orders(){

        return $this->hasMany(Order::class, 'bill_to_customer_id', 'id')
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

    public function term(){
        return $this->belongsTo(Term::class);
    }

    public function mailing_address()
    {
        return $this->hasOne(CustomerMailingAddress::class)->with(['mailing_contact', 'bill_to_contact', 'division']);
    }
}

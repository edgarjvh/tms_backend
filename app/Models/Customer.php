<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
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

    public function mailing_address()
    {
        return $this->hasOne(CustomerMailingAddress::class)->with(['mailing_contact', 'bill_to_contact', 'division']);
    }

    public function total_customer_ratings()
    {
        return $this->hasManyDeep(OrderCustomerRating::class, [Order::class], ['bill_to_customer_id'], ['id'])
            ->whereRaw('orders.is_imported = 0');
    }

    public function total_credit_ordered_ratings()
    {
        $ratings = OrderCustomerRating::query();

        $ratings->whereHas('order', function ($query1){
            return $query1
                ->where('is_imported', 0)
                ->where('order_invoiced', 0)
                ->whereNull('customer_check_number')
                ->whereHas('bill_to_company', function ($query2) {
                    return $query2->whereRaw("CONCAT(`code`,`code_number`) like '$this->bill_to_code%'");
                });
        });

        return $ratings->get();
    }

    public function getCreditOrderedAttribute()
    {
        return $this->total_credit_ordered_ratings()->sum('total_charges');
    }

    public function total_credit_invoiced_ratings()
    {
        $ratings = OrderCustomerRating::query();

        $ratings->whereHas('order', function ($query1){
            return $query1
                ->where('is_imported', 0)
                ->where('order_invoiced', 1)
                ->whereNull('customer_check_number')
                ->whereHas('bill_to_company', function ($query2) {
                    return $query2->whereRaw("CONCAT(`code`,`code_number`) like '$this->bill_to_code%'");
                });
        });

        return $ratings->get();
    }

    public function getCreditInvoicedAttribute()
    {
        return $this->total_credit_invoiced_ratings()->sum('total_charges');
    }

    public function total_credit_paid_ratings()
    {
        return $this->hasManyDeep(OrderCustomerRating::class, [Order::class], ['bill_to_customer_id'], ['id'])
            ->whereRaw('orders.is_imported = 0')
            ->whereRaw('orders.customer_check_number IS NOT NULL');
    }

    public function getCreditPaidAttribute()
    {
        return $this->total_credit_paid_ratings()->sum('total_charges');
    }

    public function getTotalCustomerOrderAttribute()
    {
        return $this->total_customer_ratings()->sum('total_charges');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class)->orderBy('first_name', 'asc');
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class)->with(['notes', 'user_code']);
    }

    public function directions()
    {
        return $this->hasMany(Direction::class)->with(['user_code']);
    }

    public function hours()
    {
        return $this->hasOne(CustomerHour::class);
    }

    public function automatic_emails()
    {
        return $this->hasMany(AutomaticEmail::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class)->with(['user_code']);
    }

    public function zip_data()
    {
        return $this->belongsTo(ZipCode::class, 'zip', 'zip_code', 'us_zipcodes');
    }

    public function orders()
    {

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

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function division(){
        return $this->belongsTo(Division::class);
    }

    public function salesman(){
        return $this->belongsTo(Salesman::class, 'salesman_id', 'id')->select(['id', DB::raw("CONCAT(`first_name`, ' ', `last_name`) AS name")]);
    }
}

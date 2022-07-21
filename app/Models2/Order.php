<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */
class Order extends Model
{

    protected array $guarded = [];
    protected string $table = 'orders';
    protected array $appends = ['total_customer_rating', 'total_carrier_rating', 'distance_mi', 'distance_km'];

    public function bill_to_company()
    {
        return $this->belongsTo(Customer::class, 'bill_to_customer_id', 'id')
            ->with(['contacts', 'term']);
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class)
            ->with(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address', 'documents', 'equipments_information']);
    }

    public function driver()
    {
        return $this->belongsTo(CarrierDriver::class, 'carrier_driver_id', 'id')->with(['equipment']);
    }

    public function notes_for_driver()
    {
        return $this->hasMany(NotesForDriver::class, 'order_id', 'id');
    }

    public function notes_for_carrier()
    {
        return $this->hasMany(NotesForCarrier::class, 'order_id', 'id');
    }

    public function internal_notes()
    {
        return $this->hasMany(InternalNotes::class, 'order_id', 'id');
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class)->with(['customer']);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class)->with(['customer']);
    }

    public function routing()
    {
        return $this->hasMany(Route::class)->with(['customer']);
    }

    public function documents()
    {
        return $this->hasMany(OrderDocument::class)->with('notes');
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class)
            ->with(['shipper', 'consignee', 'arrived_customer', 'departed_customer', 'old_carrier', 'new_carrier', 'event_type'])
            ->orderBy('updated_at', 'desc');
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->orderBy('name', 'asc');
    }

    public function load_type()
    {
        return $this->belongsTo(LoadType::class)->orderBy('name', 'asc');
    }

    public function template()
    {
        return $this->belongsTo(Template::class)->orderBy('name', 'asc');
    }

    public function order_customer_ratings()
    {
        return $this->hasMany(OrderCustomerRating::class, 'order_id', 'id')
            ->with(['rate_type', 'rate_subtype']);
    }

    public function order_carrier_ratings()
    {
        return $this->hasMany(OrderCarrierRating::class, 'order_id', 'id')
            ->with(['rate_type', 'rate_subtype']);
    }

    public function getTotalCustomerRatingAttribute()
    {
        return $this->order_customer_ratings()->sum('total_charges');
    }

    public function getTotalCarrierRatingAttribute()
    {
        return $this->order_carrier_ratings()->sum('total_charges');
    }

    public function getDistanceMiAttribute()
    {
        return (float)str_replace(',', '', number_format($this->miles > 0 ? $this->miles / 1609.34 : 0));
    }

    public function getDistanceKmAttribute()
    {
        return (float)str_replace(',', '', number_format($this->miles > 0 ? $this->miles / 1000 : 0));
    }

    public function billing_documents(){
        return $this->hasMany(OrderBillingDocument::class)->with(['notes']);
    }

    public function billing_notes(){
        return $this->hasMany(OrderBillingNote::class);
    }

    public function equipment(){
        return $this->belongsTo(Equipment::class);
    }

    public function term(){
        return $this->belongsTo(Term::class);
    }

    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
}

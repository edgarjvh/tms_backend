<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Template extends Model
{
    protected $guarded = [];
    protected $table = 'templates';
    protected $appends = ['total_customer_rating', 'total_carrier_rating', 'distance_mi', 'distance_km'];

    public function bill_to_company()
    {
        return $this->belongsTo(Customer::class, 'bill_to_customer_id', 'id')
            ->with(['contacts']);
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

    public function notes_for_carrier()
    {
        return $this->hasMany(TemplateNotesForCarrier::class, 'template_id', 'id');
    }

    public function internal_notes()
    {
        return $this->hasMany(TemplateInternalNotes::class, 'template_id', 'id');
    }

    public function pickups()
    {
        return $this->hasMany(TemplatePickup::class)->with(['customer']);
    }

    public function deliveries()
    {
        return $this->hasMany(TemplateDelivery::class)->with(['customer']);
    }

    public function routing()
    {
        return $this->hasMany(TemplateRoute::class)->with(['customer']);
    }

    public function division()
    {
        return $this->belongsTo(Division::class)->orderBy('name', 'asc');
    }

    public function load_type()
    {
        return $this->belongsTo(LoadType::class)->orderBy('name', 'asc');
    }

    public function order_customer_ratings()
    {
        return $this->hasMany(TemplateOrderCustomerRating::class, 'template_id', 'id')
            ->with(['rate_type', 'rate_subtype']);
    }

    public function order_carrier_ratings()
    {
        return $this->hasMany(TemplateOrderCarrierRating::class, 'template_id', 'id')
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

    public function equipment(){
        return $this->belongsTo(Equipment::class);
    }
}

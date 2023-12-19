<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoadBoardOrder extends Model
{
    protected $guarded = [];
    protected $table = 'orders';
    protected $appends = ['total_delivered_events'];

    public function scopeBooked($query): bool{
        return ($query->whereHas('events', function ($query1){
            return $query1->whereHas('event_type', function ($query2){
                return $query2->where('name', 'loaded');
            });
        })->count() === 0);
    }

    public function scopeTransit($query): bool{
        return $this->events()->whereHas('event_type', function ($query) {
                return $query->where('name', 'delivered');
            })->count() < $this->deliveries()->count();
    }

    public function scopeDelivered($query): bool{
        return $query->whereHas('events', function ($query1){
                return $query1->whereHas('event_type', function ($query2){
                    return $query2->where('name', 'delivered');
                });
            })->count() === $query->whereHas('deliveries')->count();
    }

    public function getTotalLoadedEventsAttribute()
    {
        return $this->events()->whereHas('event_type', function ($query) {
            return $query->where('name', 'loaded');
        })->count();
    }

    public function getTotalDeliveredEventsAttribute():int
    {
        return $this->events()->where('event_type_id', 6)->count();
    }

    public function bill_to_company(){
        return $this->belongsTo(Customer::class,'bill_to_customer_id', 'id');
    }

    public function carrier(){
        return $this->belongsTo(Carrier::class)->with(['drivers', 'contacts', 'insurances']);
    }

    public function pickups()
    {
        return $this->hasMany(Pickup::class, 'order_id', 'id')->with(['customer']);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'order_id', 'id')->with(['customer']);
    }

    public function routing()
    {
        return $this->hasMany(Route::class,'order_id', 'id');
    }

    public function events()
    {
        return $this->hasMany(OrderEvent::class, 'order_id', 'id');
    }

    public function user_code()
    {
        $agent_contact_id = $this->agent_contact_id ?? 0;

        return $this->belongsTo(UserCode::class)
            ->with('agent', function ($query) use ($agent_contact_id) {
                return $query->with('contacts');
            })
            ->with('employee');
    }
}

<?php

namespace App\Models;

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
    use \Awobaz\Compoships\Compoships;

    protected array $guarded = [];
    protected string $table = 'customers';
    protected array $appends = ['total_customer_order'];

    public function total_customer_ratings(){
        return $this->hasManyDeep(OrderCustomerRating::class, [Order::class], ['bill_to_customer_id'], ['id']);
    }

    public function getTotalCustomerOrderAttribute(){
        return $this->total_customer_ratings()->sum('total_charges');
    }

    public function contacts(){
        return $this->hasMany(Contact::class)->orderBy('first_name', 'asc');
    }

    public function documents(){
        return $this->hasMany(CustomerDocument::class)->with('notes');
    }

    public function directions(){
        return $this->hasMany(Direction::class);
    }

    public function hours(){
        return $this->hasOne(CustomerHour::class);
    }

    public function automatic_emails(){
        return $this->hasMany(AutomaticEmail::class);
    }

    public function notes(){
        return $this->hasMany(Note::class);
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

    public function mailing_address()
    {
        return $this->hasOne(CustomerMailingAddress::class)->with(['mailing_contact', 'bill_to_contact']);
    }
}

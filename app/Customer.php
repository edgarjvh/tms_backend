<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];
    protected $table = 'customers';
    protected $primaryKey = 'id';

    public function contacts(){
        return $this->hasMany(Contact::class)->orderBy('first_name', 'asc');
    }

    public function mailing_contact(){
        return $this->belongsTo(Contact::class,'mailing_contact_id', 'id', 'contacts');
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

    public function automaticEmails(){
        return $this->hasOne(AutomaticEmail::class);
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
                'pickups',
                'deliveries'
            ]);
    }
}

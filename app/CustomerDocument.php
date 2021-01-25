<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerDocument extends Model
{
    protected $guarded = [];
    protected $table = 'customer_documents';

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}

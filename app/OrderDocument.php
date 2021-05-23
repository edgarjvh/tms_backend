<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDocument extends Model
{
    protected $guarded = [];
    protected $table = 'order_documents';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function notes(){
        return $this->hasMany(OrderDocumentNote::class);
    }
}

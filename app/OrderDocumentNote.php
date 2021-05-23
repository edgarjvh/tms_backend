<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'order_document_notes';

    public function document(){
        return $this->belongsTo(OrderDocument::class);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomerDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'customer_document_notes';

    public function document(){
        return $this->belongsTo(CustomerDocument::class);
    }
}

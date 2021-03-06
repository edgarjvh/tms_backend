<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CarrierDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_document_notes';

    public function document(){
        return $this->belongsTo(CarrierDocument::class);
    }
}

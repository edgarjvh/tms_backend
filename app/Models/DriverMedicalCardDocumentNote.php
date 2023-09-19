<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverMedicalCardDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'driver_medical_card_document_notes';

    public function document(){
        return $this->belongsTo(DriverMedicalCardDocument::class, 'driver_medical_card_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

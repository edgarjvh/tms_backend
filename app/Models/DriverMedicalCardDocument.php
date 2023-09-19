<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverMedicalCardDocument extends Model
{
    protected $guarded = [];
    protected $table = 'driver_medical_card_documents';

    public function medical_card(){
        return $this->belongsTo(DriverMedicalCard::class, 'driver_medical_card_id', 'id');
    }

    public function notes(){
        return $this->hasMany(DriverMedicalCardDocumentNote::class, 'driver_medical_card_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

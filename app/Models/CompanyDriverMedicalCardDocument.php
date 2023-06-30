<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverMedicalCardDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_medical_card_documents';

    public function medical_card(){
        return $this->belongsTo(CompanyDriverMedicalCard::class, 'company_driver_medical_card_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyDriverMedicalCardDocumentNote::class, 'company_driver_medical_card_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

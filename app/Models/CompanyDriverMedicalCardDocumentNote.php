<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverMedicalCardDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_medical_card_document_notes';

    public function document(){
        return $this->belongsTo(CompanyDriverMedicalCardDocument::class, 'company_driver_medical_card_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

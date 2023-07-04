<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorMedicalCardDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_medical_card_document_notes';

    public function document(){
        return $this->belongsTo(CompanyOperatorMedicalCardDocument::class, 'company_operator_medical_card_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorMedicalCardDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_medical_card_documents';

    public function medical_card(){
        return $this->belongsTo(CompanyOperatorMedicalCard::class, 'company_operator_medical_card_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyOperatorMedicalCardDocumentNote::class, 'company_operator_medical_card_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

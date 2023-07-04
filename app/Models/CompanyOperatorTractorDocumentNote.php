<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorTractorDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_tractor_document_notes';

    public function document(){
        return $this->belongsTo(CompanyOperatorTractorDocument::class, 'company_operator_tractor_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorTrailerDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_trailer_document_notes';

    public function document(){
        return $this->belongsTo(CompanyOperatorTrailerDocument::class, 'company_operator_trailer_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

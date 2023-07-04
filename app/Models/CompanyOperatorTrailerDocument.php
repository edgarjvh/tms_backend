<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorTrailerDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_trailer_documents';

    public function trailer(){
        return $this->belongsTo(CompanyOperatorTrailer::class, 'company_operator_trailer_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyOperatorTrailerDocumentNote::class, 'company_operator_trailer_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

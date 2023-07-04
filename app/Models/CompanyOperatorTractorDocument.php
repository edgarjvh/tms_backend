<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorTractorDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_tractor_documents';

    public function tractor(){
        return $this->belongsTo(CompanyOperatorTractor::class, 'company_operator_tractor_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyOperatorTractorDocumentNote::class, 'company_operator_tractor_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

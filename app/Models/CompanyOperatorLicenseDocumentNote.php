<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorLicenseDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_license_document_notes';

    public function document(){
        return $this->belongsTo(CompanyOperatorLicenseDocument::class, 'company_operator_license_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

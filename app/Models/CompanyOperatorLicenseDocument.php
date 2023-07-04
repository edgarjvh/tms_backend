<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorLicenseDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_operator_license_documents';

    public function license(){
        return $this->belongsTo(CompanyOperatorLicense::class, 'company_operator_license_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyOperatorLicenseDocumentNote::class, 'company_operator_license_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverLicenseDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_license_document_notes';

    public function document(){
        return $this->belongsTo(CompanyDriverLicenseDocument::class, 'company_driver_license_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

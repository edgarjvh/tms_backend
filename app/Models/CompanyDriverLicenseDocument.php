<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverLicenseDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_license_documents';

    public function license(){
        return $this->belongsTo(CompanyDriverLicense::class, 'company_driver_license_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyDriverLicenseDocumentNote::class, 'company_driver_license_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

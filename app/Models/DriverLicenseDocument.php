<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLicenseDocument extends Model
{
    protected $guarded = [];
    protected $table = 'driver_license_documents';

    public function license(){
        return $this->belongsTo(DriverLicense::class, 'driver_license_id', 'id');
    }

    public function notes(){
        return $this->hasMany(DriverLicenseDocumentNote::class, 'driver_license_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

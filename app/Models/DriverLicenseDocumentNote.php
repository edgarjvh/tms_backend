<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLicenseDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'driver_license_document_notes';

    public function document(){
        return $this->belongsTo(DriverLicenseDocument::class, 'driver_license_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

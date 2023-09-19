<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverTractorDocument extends Model
{
    protected $guarded = [];
    protected $table = 'driver_tractor_documents';

    public function tractor(){
        return $this->belongsTo(DriverTractor::class, 'driver_tractor_id', 'id');
    }

    public function notes(){
        return $this->hasMany(DriverTractorDocumentNote::class, 'driver_tractor_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

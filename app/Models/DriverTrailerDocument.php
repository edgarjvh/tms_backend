<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverTrailerDocument extends Model
{
    protected $guarded = [];
    protected $table = 'driver_trailer_documents';

    public function trailer(){
        return $this->belongsTo(DriverTrailer::class, 'driver_trailer_id', 'id');
    }

    public function notes(){
        return $this->hasMany(DriverTrailerDocumentNote::class, 'driver_trailer_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

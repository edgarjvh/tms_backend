<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverTrailerDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'driver_trailer_document_notes';

    public function document(){
        return $this->belongsTo(DriverTrailerDocument::class, 'driver_trailer_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

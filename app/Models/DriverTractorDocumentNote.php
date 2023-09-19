<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverTractorDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'driver_tractor_document_notes';

    public function document(){
        return $this->belongsTo(DriverTractorDocument::class, 'driver_tractor_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

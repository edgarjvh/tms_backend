<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverTrailerDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_trailer_document_notes';

    public function document(){
        return $this->belongsTo(CompanyDriverTrailerDocument::class, 'company_driver_trailer_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

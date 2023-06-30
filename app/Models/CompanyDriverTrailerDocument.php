<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverTrailerDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_trailer_documents';

    public function trailer(){
        return $this->belongsTo(CompanyDriverTrailer::class, 'company_driver_trailer_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyDriverTrailerDocumentNote::class, 'company_driver_trailer_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

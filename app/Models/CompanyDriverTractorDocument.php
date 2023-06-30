<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverTractorDocument extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_tractor_documents';

    public function tractor(){
        return $this->belongsTo(CompanyDriverTractor::class, 'company_driver_tractor_id', 'id');
    }

    public function notes(){
        return $this->hasMany(CompanyDriverTractorDocumentNote::class, 'company_driver_tractor_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

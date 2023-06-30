<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDriverTractorDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_tractor_document_notes';

    public function document(){
        return $this->belongsTo(CompanyDriverTractorDocument::class, 'company_driver_tractor_document_id', 'id');
    }
    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

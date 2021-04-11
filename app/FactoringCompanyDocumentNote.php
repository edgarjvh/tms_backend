<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompanyDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_document_notes';

    public function document(){
        return $this->belongsTo(FactoringCompanyDocument::class);
    }
}

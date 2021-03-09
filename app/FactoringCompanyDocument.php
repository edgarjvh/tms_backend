<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FactoringCompanyDocument extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_documents';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class)->with(['documents', 'contacts', 'invoices', 'carriers', 'mailing_address', 'notes']);
    }

    public function notes(){
        return $this->hasMany(FactoringCompanyDocumentNote::class);
    }
}

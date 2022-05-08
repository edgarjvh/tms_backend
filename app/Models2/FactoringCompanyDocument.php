<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class FactoringCompanyDocument extends Model
{
    protected array $guarded = [];
    protected string $table = 'factoring_company_documents';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class)->with(['documents', 'contacts', 'invoices', 'carriers', 'mailing_address', 'notes']);
    }

    public function notes(){
        return $this->hasMany(FactoringCompanyDocumentNote::class);
    }
}

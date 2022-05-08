<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class FactoringCompanyDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'factoring_company_document_notes';

    public function document(){
        return $this->belongsTo(FactoringCompanyDocument::class);
    }
}

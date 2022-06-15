<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class OperatorDocumentNote extends Model
{
    protected array $guarded = [];
    protected string $table = 'company_operator_document_notes';

    public function document(){
        return $this->belongsTo(OperatorDocument::class);
    }
}

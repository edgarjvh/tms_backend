<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class OperatorDocument extends Model
{
    protected array $guarded = [];
    protected string $table = 'company_operator_documents';

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function notes(){
        return $this->hasMany(OperatorDocumentNote::class, 'company_operator_document_id', 'id');
    }
}

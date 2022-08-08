<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
/**
 * Post
 *
 * @mixin Builder
 */
class DivisionDocumentNote extends Model
{
    protected array $guarded = [];
    protected string $table = 'division_document_notes';

    public function document(){
        return $this->belongsTo(DivisionDocument::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

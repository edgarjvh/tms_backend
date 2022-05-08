<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CustomerDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'customer_document_notes';

    public function document(){
        return $this->belongsTo(CustomerDocument::class);
    }
}

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
    protected array $guarded = [];
    protected string $table = 'customer_document_notes';

    public function document(){
        return $this->belongsTo(CustomerDocument::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class OrderDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'order_document_notes';

    public function document(){
        return $this->belongsTo(OrderDocument::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

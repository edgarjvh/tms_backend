<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class DriverDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_driver_document_notes';

    public function document(){
        return $this->belongsTo(DriverDocument::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

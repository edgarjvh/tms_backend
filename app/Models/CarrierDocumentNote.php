<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CarrierDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_document_notes';

    public function document(){
        return $this->belongsTo(CarrierDocument::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

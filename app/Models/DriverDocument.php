<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class DriverDocument extends Model
{
    protected $guarded = [];
    protected $table = 'driver_documents';

    public function driver(){
        return $this->belongsTo(Driver::class);
    }

    public function notes(){
        return $this->hasMany(DriverDocumentNote::class, 'driver_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

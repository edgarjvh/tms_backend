<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CarrierDocument extends Model
{
    protected $guarded = [];
    protected $table = 'carrier_documents';

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }

    public function notes(){
        return $this->hasMany(CarrierDocumentNote::class);
    }
}

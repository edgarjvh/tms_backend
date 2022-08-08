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
    protected array $guarded = [];
    protected string $table = 'carrier_documents';

    public function carrier(){
        return $this->belongsTo(Carrier::class);
    }

    public function notes(){
        return $this->hasMany(CarrierDocumentNote::class)->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

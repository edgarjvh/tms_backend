<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class OrderDocument extends Model
{
    protected $guarded = [];
    protected $table = 'order_documents';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function notes(){
        return $this->hasMany(OrderDocumentNote::class)->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

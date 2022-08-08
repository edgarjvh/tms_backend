<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class InternalNotes extends Model
{
    protected $guarded = [];
    protected $table = 'order_internal_notes';

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

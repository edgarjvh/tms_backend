<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Note extends Model
{
    protected $guarded = [];
    protected $table = 'customer_notes';

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

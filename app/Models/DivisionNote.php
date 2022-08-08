<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */

class DivisionNote extends Model
{
    protected $guarded = [];
    protected $table = 'division_notes';

    public function division(){
        return $this->belongsTo(Division::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

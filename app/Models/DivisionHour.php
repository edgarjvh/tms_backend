<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */

class DivisionHour extends Model
{
    protected $guarded = [];
    protected $table = 'division_hours';

    public function division(){
        return $this->belongsTo(Division::class);
    }
}

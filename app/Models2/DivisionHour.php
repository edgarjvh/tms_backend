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
    protected array $guarded = [];
    protected string $table = 'division_hours';

    public function division(){
        return $this->belongsTo(Division::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class InsuranceType extends Model
{
    protected array $guarded = [];

    public function insurance(){
        return $this->hasMany(Insurance::class);
    }
}

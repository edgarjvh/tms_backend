<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class LoadType extends Model
{
    protected $guarded = [];
    protected $table = 'load_types';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class ZipCode extends Model
{
    protected $guarded = [];
    protected $table = 'us_zipcodes';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class RateSubtype extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'rate_subtypes';
}

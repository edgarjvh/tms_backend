<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */
class Config extends Model
{
    protected array $guarded = [];
    protected string $table = 'config';
}

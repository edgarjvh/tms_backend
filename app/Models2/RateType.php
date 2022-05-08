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

class RateType extends Model
{
    use HasFactory;
    protected array $guarded = [];
    protected string $table = 'rate_types';
}

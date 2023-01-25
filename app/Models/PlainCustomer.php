<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * Post
 *
 * @mixin Builder
 */
class PlainCustomer extends Model
{
    use HasRelationships;
    use Compoships;

    protected $guarded = [];
    protected $table = 'customers';
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CustomerHour extends Model
{
    protected array $guarded = [];
    protected string $table = 'customer_hours';

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}

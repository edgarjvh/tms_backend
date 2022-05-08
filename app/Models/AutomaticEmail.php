<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class AutomaticEmail extends Model
{
    protected $guarded = [];
    protected $table = 'customer_automatic_emails';

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
}

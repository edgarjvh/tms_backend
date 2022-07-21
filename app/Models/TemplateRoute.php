<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class TemplateRoute extends Model
{
    protected $guarded = [];
    protected $table = 'template_order_routing';

    public function customer(){
        return $this->belongsTo(Customer::class)
            ->with(['contacts', 'zip_data']);
    }
}

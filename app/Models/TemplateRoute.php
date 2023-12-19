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

    public function template(){
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    public function customer(){
        return $this->belongsTo(Customer::class)
            ->with(['zip_data']);
    }

    public function pickup(){
        return $this->belongsTo(TemplatePickup::class, 'pickup_id', 'id')->with(['customer']);
    }

    public function delivery(){
        return $this->belongsTo(TemplateDelivery::class, 'delivery_id', 'id')->with(['customer']);
    }
}

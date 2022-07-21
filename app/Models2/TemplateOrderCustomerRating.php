<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */

class TemplateOrderCustomerRating extends Model
{
    protected array $guarded = [];
    protected string $table = 'template_order_customer_ratings';

    public function template(){
        return $this->belongsTo(Template::class);
    }

    public function rate_type(){
        return $this->belongsTo(RateType::class);
    }

    public function rate_subtype(){
        return $this->belongsTo(RateSubtype::class);
    }
}

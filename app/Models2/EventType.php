<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class EventType extends Model
{
    protected array $guarded = [];
    protected string $table = 'event_types';

    public function order_events(){
        return $this->hasMany(OrderEvent::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class TemplatePickup extends Model
{
    protected array $guarded = [];
    protected string $table = 'template_order_pickups';

    public function customer(){
        return $this->belongsTo(Customer::class)
            ->with(['contacts', 'zip_data', 'directions']);
    }

    public function template(){
        return $this->belongsTo(Template::class)
            ->with([
                'bill_to_company',
                'carrier',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'division',
                'load_type',
                'order_customer_ratings',
                'order_carrier_ratings',
            ]);
    }
}

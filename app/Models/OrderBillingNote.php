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
class OrderBillingNote extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'order_billing_notes';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

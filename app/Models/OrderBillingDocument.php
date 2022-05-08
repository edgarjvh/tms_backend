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
class OrderBillingDocument extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'order_billing_documents';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function notes(){
        return $this->hasMany(OrderBillingDocumentNote::class, 'order_billing_document_id', 'id');
    }
}

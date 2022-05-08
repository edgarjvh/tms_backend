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
class OrderInvoiceCarrierDocument extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'order_invoice_carrier_documents';

    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function notes(){
        return $this->hasMany(OrderInvoiceCarrierDocumentNote::class, 'order_invoice_carrier_document_id', 'id');
    }
}

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
class OrderInvoiceCarrierDocumentNote extends Model
{
    use HasFactory;
    protected array $guarded = [];
    protected string $table = 'order_invoice_carrier_document_notes';

    public function order(){
        return $this->belongsTo(OrderInvoiceCarrierDocument::class,'order_invoice_carrier_document_id', 'id');
    }
}

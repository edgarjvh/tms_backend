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
class OrderInvoiceCustomerDocumentNote extends Model
{
    use HasFactory;
    protected array $guarded = [];
    protected string $table = 'order_invoice_customer_document_notes';

    public function order(){
        return $this->belongsTo(OrderInvoiceCustomerDocument::class,'order_invoice_customer_document_id', 'id');
    }
}

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
class OrderInvoiceInternalNote extends Model
{
    use HasFactory;
    protected array $guarded = [];
    protected string $table = 'order_invoice_internal_notes';

    public function order(){
        return $this->belongsTo(Order::class);
    }
}

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
class OrderBillingDocumentNote extends Model
{
    use HasFactory;
    protected array $guarded = [];
    protected string $table = 'order_billing_document_notes';

    public function order(){
        return $this->belongsTo(OrderBillingDocument::class,'order_billing_document_id', 'id');
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

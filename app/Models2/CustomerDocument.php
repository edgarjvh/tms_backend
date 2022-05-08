<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CustomerDocument extends Model
{
    protected array $guarded = [];
    protected string $table = 'customer_documents';

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function notes(){
        return $this->hasMany(CustomerDocumentNote::class);
    }
}

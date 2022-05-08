<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Contact extends Model
{
    protected array $guarded = [];
    protected string $table = 'customer_contacts';

    public function customer(){
        return $this->belongsTo(Customer::class)->with(['contacts', 'documents', 'directions', 'hours', 'automatic_emails', 'notes']);
    }
}

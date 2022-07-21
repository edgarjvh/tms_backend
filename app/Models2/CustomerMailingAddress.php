<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class CustomerMailingAddress extends Model
{
    use \Awobaz\Compoships\Compoships;

    protected array $guarded = [];
    protected string $table = 'customer_mailing_addresses';

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function mailing_contact(){
        return $this->belongsTo(Contact::class,'mailing_contact_id', 'id', 'customer_contacts');
    }

    public function bill_to_contact(){
        return $this->belongsTo(Customer::class, ['bill_to_code', 'bill_to_code_number'], ['code', 'code_number'], 'customers')
            ->with(['contacts']);
    }

    public function division(){
        return $this->belongsTo(Division::class);
    }
}

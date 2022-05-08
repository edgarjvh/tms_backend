<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class FactoringCompanyInvoice extends Model
{
    protected array $guarded = [];
    protected string $table = 'factoring_company_invoices';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class);
    }
}

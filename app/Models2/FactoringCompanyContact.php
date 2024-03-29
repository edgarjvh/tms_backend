<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 * @property mixed factoring_company_id
 * @property mixed first_name
 */

class FactoringCompanyContact extends Model
{
    protected array $guarded = [];
    protected string $table = 'contacts';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class)->with(['documents','contacts', 'invoices', 'carriers', 'mailing_address', 'notes']);
    }
}

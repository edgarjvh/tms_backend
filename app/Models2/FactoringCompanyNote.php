<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class FactoringCompanyNote extends Model
{
    protected array $guarded = [];
    protected string $table = 'factoring_company_notes';

    public function factoring_company(){
        return $this->belongsTo(FactoringCompany::class);
    }
}

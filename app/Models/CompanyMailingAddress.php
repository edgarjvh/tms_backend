<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMailingAddress extends Model
{
    protected $guarded = [];
    protected $table = 'company_mailing_addresses';

    public function company () {
        return $this->belongsTo(Company::class);
    }
}

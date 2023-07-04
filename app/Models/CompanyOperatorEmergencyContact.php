<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOperatorEmergencyContact extends Model
{
    protected $guarded = [];
    protected $table = 'contacts';
    protected $appends = ['name'];

    public function company_operator()
    {
        return $this->belongsTo(CompanyOperator::class, 'operator_id', 'id');
    }

    public function getNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}

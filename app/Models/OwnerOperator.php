<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerOperator extends Model
{
    protected $guarded = [];
    protected $table = 'company_operators';

    public function company(){
        return $this->belongsTo(Company::class)->with(['operators']);
    }

    public function documents(){
        return $this->hasMany(OperatorDocument::class)->with(['notes', 'user_code']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class Division extends Model
{
    protected $guarded = [];
    protected $table = 'divisions';

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function contacts(){
        return $this->hasMany(Contact::class)->orderBy('first_name')->orderBy('last_name');
    }

    public function notes(){
        return $this->hasMany(DivisionNote::class)->with(['user_code']);
    }

    public function mailing_address(){
        return $this->hasOne(DivisionMailingAddress::class)->with(['mailing_contact']);
    }

    public function hours(){
        return $this->hasOne(DivisionHour::class);
    }

    public function documents(){
        return $this->hasMany(DivisionDocument::class)->with(['notes', 'user_code']);
    }
}

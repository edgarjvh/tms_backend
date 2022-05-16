<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $guarded = [];
    protected $table = 'company_agents';

    public function company(){
        return $this->belongsTo(Company::class)->with(['agents']);
    }
}

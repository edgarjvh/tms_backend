<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class AgentDriver extends Model
{
    protected $guarded = [];
    protected $table = 'company_agent_drivers';

    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    public function equipment(){
        return $this->belongsTo(Equipment::class);
    }
}

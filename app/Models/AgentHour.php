<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */
class AgentHour extends Model
{
    protected $guarded = [];
    protected $table = 'company_agent_hours';

    public function agent(){
        return $this->belongsTo(Agent::class);
    }
}

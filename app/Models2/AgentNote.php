<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */

class AgentNote extends Model
{
    protected array $guarded = [];
    protected string $table = 'company_agent_notes';

    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}

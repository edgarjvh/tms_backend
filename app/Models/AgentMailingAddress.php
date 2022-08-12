<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Post
 *
 * @mixin Builder
 */
class AgentMailingAddress extends Model
{
    use Compoships;

    protected $guarded = [];
    protected $table = 'company_agent_mailing_addresses';

    public function agent(){
        return $this->belongsTo(Agent::class);
    }

    public function mailing_contact(){
        return $this->belongsTo(AgentContact::class,'mailing_contact_id', 'id', 'contacts');
    }
}

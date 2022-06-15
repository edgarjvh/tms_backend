<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class AgentDocumentNote extends Model
{
    protected $guarded = [];
    protected $table = 'company_agent_document_notes';

    public function document(){
        return $this->belongsTo(AgentDocument::class);
    }
}

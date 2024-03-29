<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Post
 *
 * @mixin Builder
 */

class AgentDocument extends Model
{
    protected array $guarded = [];
    protected string $table = 'company_agent_documents';

    public function company(){
        return $this->belongsTo(Company::class);
    }

    public function notes(){
        return $this->hasMany(AgentDocumentNote::class, 'company_agent_document_id', 'id')->with(['user_code']);
    }

    public function user_code(){
        return $this->belongsTo(UserCode::class);
    }
}
